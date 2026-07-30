<?php

namespace App\Http\Controllers\Yayasan;

use App\Http\Controllers\Controller;
use App\Models\FoundationLetter;
use App\Models\FoundationLetterRead;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoundationLetterController extends Controller
{
    public const CATEGORIES = [
        'edaran'        => 'Surat Edaran',
        'pemberitahuan' => 'Surat Pemberitahuan',
        'undangan'      => 'Surat Undangan',
        'sk'            => 'Surat Keputusan (SK)',
        'teguran'       => 'Surat Teguran',
        'permohonan'    => 'Surat Permohonan',
        'kerjasama'     => 'Surat Kerja Sama',
    ];

    public const TARGET_AUDIENCES = [
        'kepala_sekolah' => 'Kepala Sekolah se-Perguruan Pembda Nias',
        'guru_pegawai'   => 'Bapak/Ibu Guru dan Pegawai se-Perguruan Pembda Nias',
        'siswa'          => 'Siswa/Siswi se-Perguruan Pembda Nias',
        'pengurus'       => 'Pengurus Yayasan Perguruan Pembda Nias',
        'pembina'        => 'Pembina Yayasan Perguruan Pembda Nias',
    ];

    public function index()
    {
        $letters = FoundationLetter::withCount('reads')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = self::CATEGORIES;
        $targetAudiences = self::TARGET_AUDIENCES;

        return view('yayasan.letters.index', compact('letters', 'categories', 'targetAudiences'));
    }

    public function create(Request $request)
    {
        $categories = self::CATEGORIES;
        $targetAudiences = self::TARGET_AUDIENCES;
        $schools = School::whereIn('id', [1, 2, 3])->get();

        // Default / Preset Content if requested
        $preset = $request->query('preset');
        $defaultNumber = '045/SE-YAY/PEMBDA/VII/' . date('Y');
        $defaultTitle = 'Penetapan Standar Minimal Progress Input Data, Kesiapan LMS Kelas Eksperimen, dan Implementasi Modul PKL TA 2026/2027';
        $defaultEffectiveDate = date('Y-m-d');
        $defaultDeadlineDate = date('Y-08-03');
        $defaultCategory = 'edaran';
        $defaultTargets = ['kepala_sekolah'];

        $defaultContent = '';
        if ($preset === 'standar_input') {
            $defaultContent = '<p>Dengan hormat,</p>
<p>Sehubungan dengan dimulainya Tahun Ajaran 2026/2027 serta dalam rangka optimalisasi digitalisasi tata kelola sekolah berbasis sistem PembdaHUB, Yayasan Perguruan PEMBDA Nias menetapkan Standar Minimal Progress Input Data yang wajib dipenuhi oleh setiap unit sekolah paling lambat pada:</p>

<p style="text-align: center; background-color: #f8fafc; padding: 12px; border-left: 4px solid #6d28d9; margin: 15px 0;">
<strong>📅 Hari/Tanggal : Senin, 3 Agustus 2026<br>
⏰ Waktu : Pukul 23.59 WIB</strong>
</p>

<p>Adapun rincian Standar Minimal Progress Input Data yang harus diselesaikan oleh masing-masing unit sekolah meliputi:</p>

<ol>
    <li><strong>DATA AKADEMIK & KESISWAAN (Target: 100%)</strong>
        <ul>
            <li>Seluruh Data Kelas / Rombongan Belajar (Rombel) TA 2026/2027 beserta penetapan Wali Kelas telah selesai di-input.</li>
            <li>Seluruh Data Siswa (Siswa Baru & Siswa Naik Kelas) terdaftar 100% dan telah terdistribusi ke Rombel masing-masing.</li>
        </ul>
    </li>
    <li><strong>DATA PENGAJARAN & JADWAL (Target: 100%)</strong>
        <ul>
            <li>Pembagian Tugas Mengajar Guru (Teaching Assignment) untuk seluruh mata pelajaran telah di-input.</li>
            <li>Jadwal Pelajaran Mingguan Semester Ganjil TA 2026/2027 seluruh kelas telah terbit di sistem.</li>
        </ul>
    </li>
    <li><strong>KESIAPAN LMS - 1 KELAS EKSPERIMEN PER UNIT (Target: 100% Matpel)</strong><br>
        Setiap unit sekolah (SMP, SMA, dan SMK) WAJIB menunjuk 1 (satu) Kelas Eksperimen/Pilot Class. Untuk KELAS EKSPERIMEN tersebut, SELURUH MATA PELAJARAN wajib memiliki ketersediaan data LMS sebagai berikut:
        <ul>
            <li>Course (Mata Pelajaran Digital) sudah aktif.</li>
            <li>Modul Pembelajaran minimal untuk Bab 1 / Topik Awal Semester Ganjil.</li>
            <li>Materi Ajar (File PDF, Slide, atau Link Video) Bab 1 sudah diunggah.</li>
            <li>Tugas Pembelajaran (Minimal 1 Tugas per Matpel) sudah terkonfigurasi.</li>
            <li>Quiz / Kuis Interaktif (Minimal 1 Kuis per Matpel) sudah siap diakses siswa.</li>
        </ul>
    </li>
    <li><strong>KEUANGAN & REKAPITULASI SPP JULI 2026 (Target: 100%)</strong>
        <ul>
            <li>Seluruh Rekapitulasi Pembayaran Uang Sekolah / SPP Bulan Juli 2026 (baik pembayaran Tunai maupun Transfer) telah selesai di-entry 100% ke dalam sistem PembdaHUB.</li>
            <li>Setting Tarif SPP dan Pembayaran TA 2026/2027 telah selesai agar penerbitan tagihan SPP Bulan Agustus 2026 berjalan presisi.</li>
        </ul>
    </li>
    <li><strong>KEPEGAWAIAN & PRESENSI (Target: Minimal 90%)</strong>
        <ul>
            <li>Pengaturan Jam Kerja / Jam Presensi Guru dan Staf Pegawai telah diatur.</li>
            <li>Pemetaan ID Kartu RFID / Perangkat Presensi Guru & Pegawai telah selesai disinkronkan.</li>
        </ul>
    </li>
    <li><strong>KHUSUS UNIT SMKS SWASTA PEMBDA NIAS - IMPLEMENTASI MODUL PKL (Target: 100%)</strong>
        <ul>
            <li><strong>Sisi Siswa Peserta PKL:</strong> Pengisian Logbook / Jurnal Kegiatan Harian PKL secara aktif melalui akun siswa di PembdaHUB.</li>
            <li><strong>Sisi Guru Pendamping / Pembimbing PKL:</strong> Pelaksanaan verifikasi, monitoring catatan harian, dan pemberian penilaian logbook PKL oleh Guru Pendamping di PembdaHUB.</li>
        </ul>
    </li>
</ol>

<p><strong>MONITORING & EVALUASI:</strong><br>
Administrator PembdaHUB dan Manajemen Yayasan akan melakukan verifikasi dan penarikan laporan progress secara otomatis dari sistem PembdaHUB pada hari Selasa, 4 Agustus 2026. Hasil progress masing-masing unit sekolah akan dilaporkan langsung kepada Pengurus Yayasan sebagai bahan evaluasi kinerja unit.</p>

<p>Demikian Surat Edaran ini disampaikan untuk dilaksanakan dengan penuh rasa tanggung jawab. Atas perhatian dan kerja sama Bapak/Ibu Kepala Sekolah beserta jajaran Administrator PembdaHUB Unit, kami ucapkan terima kasih.</p>';
        }

        return view('yayasan.letters.create', compact(
            'categories',
            'targetAudiences',
            'schools',
            'defaultNumber',
            'defaultTitle',
            'defaultEffectiveDate',
            'defaultDeadlineDate',
            'defaultCategory',
            'defaultTargets',
            'defaultContent'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'letter_number'      => 'required|string|max:100',
            'title'              => 'required|string|max:255',
            'category'           => 'required|string|in:' . implode(',', array_keys(self::CATEGORIES)),
            'target_audiences'   => 'required|array|min:1',
            'target_audiences.*' => 'string|in:' . implode(',', array_keys(self::TARGET_AUDIENCES)),
            'content'            => 'required|string',
            'effective_date'     => 'required|date',
            'deadline_date'      => 'nullable|date',
            'signatory_name'     => 'required|string|max:255',
            'signatory_position' => 'required|string|max:255',
        ]);

        $hash = FoundationLetter::generateHash($request->letter_number, $request->title, $request->effective_date);

        $selectedTargets = $request->target_audiences;
        $targetLabels = [];
        foreach ($selectedTargets as $targetKey) {
            if (isset(self::TARGET_AUDIENCES[$targetKey])) {
                $targetLabels[] = self::TARGET_AUDIENCES[$targetKey];
            }
        }

        $recipientsData = [
            'target_keys'   => $selectedTargets,
            'target_labels' => $targetLabels,
            'unit_ids'      => [1, 2, 3],
        ];

        $letter = FoundationLetter::create([
            'letter_number'      => $request->letter_number,
            'title'              => $request->title,
            'category'           => $request->category,
            'content'            => $request->content,
            'effective_date'     => $request->effective_date,
            'deadline_date'      => $request->deadline_date,
            'recipients'         => $recipientsData,
            'signatory_name'     => $request->signatory_name,
            'signatory_position' => $request->signatory_position,
            'signature_hash'     => $hash,
            'signed_at'          => now(),
            'signed_by_user_id'  => Auth::id(),
            'status'             => 'published',
        ]);

        return redirect()->route('yayasan.letters.show', $letter->id)
            ->with('success', 'Surat Digital berhasil diterbitkan dan ditandatangani secara digital dengan QR Code Verifikasi!');
    }

    public function show($id)
    {
        $letter = FoundationLetter::with(['signedBy', 'reads.school', 'reads.user'])->findOrFail($id);
        $categories = self::CATEGORIES;
        $targetAudiences = self::TARGET_AUDIENCES;

        return view('yayasan.letters.show', compact('letter', 'categories', 'targetAudiences'));
    }

    public function print($id)
    {
        $letter = FoundationLetter::findOrFail($id);
        $categories = self::CATEGORIES;
        $targetAudiences = self::TARGET_AUDIENCES;

        return view('yayasan.letters.print', compact('letter', 'categories', 'targetAudiences'));
    }

    public function destroy($id)
    {
        $letter = FoundationLetter::findOrFail($id);
        $letter->delete();

        return redirect()->route('yayasan.letters.index')
            ->with('success', 'Surat Digital berhasil dihapus.');
    }
}
