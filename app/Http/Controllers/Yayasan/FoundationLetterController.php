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
        'pengurus'       => 'Pengurus Yayasan Perguruan Pembda Nias',
        'kepala_sekolah' => 'Kepala Sekolah se-Perguruan Pembda Nias',
        'guru_pegawai'   => 'Bapak/Ibu Guru dan Pegawai se-Perguruan Pembda Nias',
        'siswa'          => 'Siswa/Siswi se-Perguruan Pembda Nias',
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
        $defaultNumber = '......./SE/YP-PEMBDA/VII/' . date('Y');
        $defaultTitle = 'Penetapan Standar Minimal Progress Input Data, Kesiapan LMS Kelas Eksperimen, dan Implementasi Modul PKL TA 2026/2027';
        $defaultEffectiveDate = date('Y-m-d');
        $defaultDeadlineDate = date('Y-08-03');
        $defaultCategory = 'edaran';
        $defaultTargets = ['kepala_sekolah'];

        $defaultContent = '';
        if ($preset === 'hut_ri' || $preset === 'peringatan_hut_ri') {
            $defaultNumber = '06/SE/YP-PEMBDA/VIII/' . date('Y');
            $defaultTitle = 'Undangan & Pelaksanaan Peringatan Hari Kemerdekaan Republik Indonesia Ke-81 Tahun 2026 di Lingkungan Yayasan Perguruan Pembda Nias';
            $defaultEffectiveDate = date('Y-m-d');
            $defaultDeadlineDate = date('Y-08-17');
            $defaultCategory = 'undangan';
            $defaultTargets = ['pengurus', 'kepala_sekolah', 'guru_pegawai', 'siswa'];
            $defaultContent = '<div style="border-left: 4px solid #dc2626; padding-left: 14px; margin-bottom: 20px; background-color: #fef2f2; padding-top: 10px; padding-bottom: 10px; border-radius: 0 8px 8px 0;">
    <p style="margin: 0; font-weight: bold; color: #991b1b; text-transform: uppercase; font-size: 13px; letter-spacing: 0.05em;">
        PERINGATAN HUT KE-81 KEMERDEKAAN REPUBLIK INDONESIA
    </p>
    <p style="margin: 2px 0 0 0; font-style: italic; color: #b91c1c; font-size: 12px;">
        Tema Nasional: "Nusantara Baru, Indonesia Maju"
    </p>
</div>

<p>Dengan hormat,</p>
<p>Dalam rangka menyambut dan memeriahkan <strong>Hari Ulang Tahun (HUT) Ke-81 Kemerdekaan Republik Indonesia Tahun 2026</strong>, Pengurus Yayasan Perguruan PEMBDA Nias dengan ini <strong>MENGUNDANG DENGAN HORMAT</strong> Bapak/Ibu Pengurus Yayasan, Kepala Sekolah, Bapak/Ibu Guru, Staf Pegawai, serta Seluruh Siswa/Siswi di lingkungan Perguruan PEMBDA Nias (SMP Swasta Pembda 2 Gunungsitoli, SMA Swasta Pembda 1 Gunungsitoli, dan SMK Swasta Pembda Nias) untuk hadir dan berpartisipasi aktif dalam <strong>Upacara Bendera Peringatan HUT Ke-81 RI</strong> yang akan dilaksanakan dengan ketentuan sebagai berikut:</p>

<ol style="list-style-type: decimal; padding-left: 24px; margin-top: 15px; margin-bottom: 20px; line-height: 1.8;">
    <li style="margin-bottom: 14px;">
        <strong style="color: #991b1b; text-transform: uppercase;">PELAKSANAAN UPACARA BENDERA PERINGATAN HUT KE-81 RI</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 6px;">
            <li><strong>Hari / Tanggal:</strong> Senin, 17 Agustus 2026</li>
            <li><strong>Waktu:</strong> Pukul 07.00 WIB <em>(Seluruh peserta upacara wajib hadir di lokasi paling lambat pukul 06.45 WIB)</em></li>
            <li><strong>Tempat:</strong> Lapangan Upacara Yayasan Perguruan Pembda Nias, Jl. Pelita No. 09 Gunungsitoli</li>
            <li><strong>Peserta Upacara:</strong> Seluruh Pengurus Yayasan, Kepala Sekolah, Bapak/Ibu Guru, Staf Pegawai, serta Seluruh Siswa/Siswi SMP, SMA, dan SMK Swasta Pembda Nias.</li>
            <li><strong>Ketentuan Pakaian:</strong>
                <ul style="list-style-type: circle; padding-left: 20px; margin-top: 4px;">
                    <li><strong>Pengurus, Kepala Sekolah, Guru & Pegawai:</strong> Pakaian Nuansa Adat Nasional</li>
                    <li><strong>Siswa/Siswi:</strong> Seragam Lengkap dengan Atribut (Topi & Dasi) dan Sepatu Hitam</li>
                </ul>
            </li>
        </ul>
    </li>

    <li style="margin-bottom: 14px;">
        <strong style="color: #991b1b; text-transform: uppercase;">PETUGAS PELAKSANA UPACARA BENDERA</strong>
        <ol style="list-style-type: decimal; padding-left: 20px; margin-top: 6px; line-height: 1.2;">
            <li style="margin-bottom: 3px;"><strong>Pengibar Bendera:</strong> Paskibraka Sekolah</li>
            <li style="margin-bottom: 3px;"><strong>Pembina Upacara:</strong> Ketua Yayasan Perguruan PEMBDA Nias</li>
            <li style="margin-bottom: 3px;"><strong>Pemimpin Upacara:</strong> Guru</li>
            <li style="margin-bottom: 3px;"><strong>Pembaca Teks Proklamasi:</strong> Guru Senior</li>
            <li style="margin-bottom: 3px;"><strong>Paduan Suara:</strong> Gabungan Bapak/Ibu Guru dari ke-3 Unit Sekolah</li>
            <li style="margin-bottom: 3px;"><strong>Perangkat Upacara Lainnya:</strong> Siswa</li>
        </ol>
    </li>

    <li style="margin-bottom: 14px;">
        <strong style="color: #991b1b; text-transform: uppercase;">RANGKAIAN ACARA SETELAH UPACARA BENDERA</strong>
        <ol style="list-style-type: decimal; padding-left: 20px; margin-top: 6px; line-height: 1.2;">
            <li style="margin-bottom: 3px;">Penampilan Atraksi Marchingband Perguruan PEMBDA Nias</li>
            <li style="margin-bottom: 3px;">Ramah Tamah & Syukuran Kemerdekaan</li>
            <li style="margin-bottom: 3px;">Sesi Foto Bersama Seluruh Keluarga Besar Perguruan PEMBDA Nias</li>
        </ol>
    </li>

    <li style="margin-bottom: 14px;">
        <strong style="color: #991b1b; text-transform: uppercase;">KEDISIPLINAN & PRESENSI DIGITAL PEMBDAHUB</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 6px;">
            <li>Seluruh Guru & Pegawai <strong>WAJIB</strong> melakukan Presensi Masuk Upacara melalui sistem Presensi Digital PembdaHUB / TAP Kartu RFID di lokasi upacara.</li>
            <li>Kepala Sekolah bertanggung jawab penuh atas rekapitulasi kehadiran fisik serta ketertiban barisan unit sekolah masing-masing.</li>
        </ul>
    </li>

    <li style="margin-bottom: 14px;">
        <strong style="color: #991b1b; text-transform: uppercase;">PERSIAPAN UNIT SEKOLAH (SMP, SMA, SMK)</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 6px;">
            <li>Masing-masing Kepala Sekolah beserta seluruh jajaran unit sekolah diwajibkan untuk segera melakukan persiapan internal secara optimal (termasuk koordinasi gladi bersih Paskibraka, latihan Paduan Suara, latihan Marchingband, kerapian seragam siswa, serta kesiapan perangkat presensi digital).</li>
        </ul>
    </li>
</ol>

<p style="margin-top: 18px;"><strong>PENUTUP:</strong><br>
Mengingat pentingnya acara ini sebagai wujud penghormatan, jiwa nasionalisme, dan rasa syukur atas kemerdekaan Bangsa Indonesia, kehadiran dan persiapan matang seluruh unit sekolah tepat pada waktunya sangat diharapkan. Atas perhatian, kehadiran, dan kerja sama yang baik, kami ucapkan terima kasih.</p>

<div style="margin-top: 18px; padding: 12px; text-align: center; border-top: 2px dashed #ef4444; border-bottom: 2px dashed #ef4444; background-color: #fafafa; border-radius: 6px;">
    <p style="margin: 0; font-weight: bold; color: #b91c1c; font-size: 14px; text-transform: uppercase; letter-spacing: 0.08em;">
        DIRGAHAYU REPUBLIK INDONESIA KE-81
    </p>
    <p style="margin: 4px 0 0 0; font-weight: 800; color: #111827; font-size: 13px;">
        <em>MERDEKA! MERDEKA! MERDEKA!</em>
    </p>
</div>';
        } elseif ($preset === 'standar_input') {
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

    public function edit($id)
    {
        $letter = FoundationLetter::findOrFail($id);
        $categories = self::CATEGORIES;
        $targetAudiences = self::TARGET_AUDIENCES;
        $schools = School::whereIn('id', [1, 2, 3])->get();

        $selectedTargets = is_array($letter->recipients) && isset($letter->recipients['target_keys'])
            ? $letter->recipients['target_keys']
            : ['kepala_sekolah'];

        return view('yayasan.letters.edit', compact(
            'letter',
            'categories',
            'targetAudiences',
            'schools',
            'selectedTargets'
        ));
    }

    public function update(Request $request, $id)
    {
        $letter = FoundationLetter::findOrFail($id);

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

        $letter->update([
            'letter_number'      => $request->letter_number,
            'title'              => $request->title,
            'category'           => $request->category,
            'content'            => $request->content,
            'effective_date'     => $request->effective_date,
            'deadline_date'      => $request->deadline_date,
            'recipients'         => $recipientsData,
            'signatory_name'     => $request->signatory_name,
            'signatory_position' => $request->signatory_position,
        ]);

        return redirect()->route('yayasan.letters.show', $letter->id)
            ->with('success', 'Surat Digital berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $letter = FoundationLetter::findOrFail($id);
        $letter->delete();

        return redirect()->route('yayasan.letters.index')
            ->with('success', 'Surat Digital berhasil dihapus.');
    }
}
