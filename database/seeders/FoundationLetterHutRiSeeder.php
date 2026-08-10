<?php

namespace Database\Seeders;

use App\Models\FoundationLetter;
use App\Models\User;
use Illuminate\Database\Seeder;

class FoundationLetterHutRiSeeder extends Seeder
{
    public function run(): void
    {
        $letterNumber = '06/SE/YP-PEMBDA/VIII/2026';
        $title = 'Undangan & Pelaksanaan Peringatan Hari Kemerdekaan Republik Indonesia Ke-81 Tahun 2026 di Lingkungan Yayasan Perguruan Pembda Nias';
        $effectiveDate = '2026-08-10';
        $deadlineDate = '2026-08-17';

        $signatoryName = 'Yulianus Zega, S.Kom,M.Pd.T';
        $signatoryPosition = 'Ketua Yayasan Perguruan PEMBDA Nias';

        $user = User::where('role', 'superadmin')->first() ?? User::first();

        $content = '<div style="border-left: 4px solid #dc2626; padding-left: 14px; margin-bottom: 16px; background-color: #fef2f2; padding-top: 8px; padding-bottom: 8px; border-radius: 0 8px 8px 0; line-height: 1.25;">
    <p style="margin: 0; font-weight: bold; color: #991b1b; text-transform: uppercase; font-size: 13px; letter-spacing: 0.05em;">
        PERINGATAN HUT KE-81 KEMERDEKAAN REPUBLIK INDONESIA
    </p>
    <p style="margin: 2px 0 0 0; font-style: italic; color: #b91c1c; font-size: 12px;">
        Tema Nasional: "Nusantara Baru, Indonesia Maju"
    </p>
</div>

<p style="line-height: 1.25; margin-bottom: 8px;">Dengan hormat,</p>
<p style="line-height: 1.25; margin-bottom: 12px;">Dalam rangka menyambut dan memeriahkan <strong>Hari Ulang Tahun (HUT) Ke-81 Kemerdekaan Republik Indonesia Tahun 2026</strong>, Pengurus Yayasan Perguruan PEMBDA Nias dengan ini <strong>MENGUNDANG DENGAN HORMAT</strong> Bapak/Ibu Pengurus Yayasan, Kepala Sekolah, Bapak/Ibu Guru, Staf Pegawai, serta Seluruh Siswa/Siswi di lingkungan Perguruan PEMBDA Nias (SMP Swasta Pembda 2 Gunungsitoli, SMA Swasta Pembda 1 Gunungsitoli, dan SMK Swasta Pembda Nias) untuk hadir dan berpartisipasi aktif dalam <strong>Upacara Bendera Peringatan HUT Ke-81 RI</strong> yang akan dilaksanakan dengan ketentuan sebagai berikut:</p>

<ol style="list-style-type: decimal; padding-left: 24px; margin-top: 10px; margin-bottom: 15px; line-height: 1.25;">
    <li style="margin-bottom: 10px;">
        <strong style="color: #991b1b; text-transform: uppercase;">PELAKSANAAN UPACARA BENDERA PERINGATAN HUT KE-81 RI</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; line-height: 1.2;">
            <li style="margin-bottom: 3px;"><strong>Hari / Tanggal:</strong> Senin, 17 Agustus 2026</li>
            <li style="margin-bottom: 3px;"><strong>Waktu:</strong> Pukul 07.00 WIB <em>(Seluruh peserta upacara wajib hadir di lokasi paling lambat pukul 06.45 WIB)</em></li>
            <li style="margin-bottom: 3px;"><strong>Tempat:</strong> Lapangan Upacara Yayasan Perguruan Pembda Nias, Jl. Pelita No. 09 Gunungsitoli</li>
            <li style="margin-bottom: 3px;"><strong>Peserta Upacara:</strong> Seluruh Pengurus Yayasan, Kepala Sekolah, Bapak/Ibu Guru, Staf Pegawai, serta Seluruh Siswa/Siswi SMP, SMA, dan SMK Swasta Pembda Nias.</li>
            <li style="margin-bottom: 3px;"><strong>Ketentuan Pakaian:</strong>
                <ul style="list-style-type: circle; padding-left: 20px; margin-top: 3px; line-height: 1.2;">
                    <li style="margin-bottom: 2px;"><strong>Pengurus, Kepala Sekolah, Guru & Pegawai:</strong> Pakaian Nuansa Adat Nasional</li>
                    <li style="margin-bottom: 2px;"><strong>Siswa/Siswi:</strong> Seragam Lengkap dengan Atribut (Topi & Dasi) dan Sepatu Hitam</li>
                </ul>
            </li>
        </ul>
    </li>

    <li style="margin-bottom: 10px;">
        <strong style="color: #991b1b; text-transform: uppercase;">PETUGAS PELAKSANA UPACARA BENDERA</strong>
        <ol style="list-style-type: decimal; padding-left: 20px; margin-top: 4px; line-height: 1.2;">
            <li style="margin-bottom: 3px;"><strong>Pengibar Bendera:</strong> Paskibraka Sekolah</li>
            <li style="margin-bottom: 3px;"><strong>Pembina Upacara:</strong> Ketua Yayasan Perguruan PEMBDA Nias</li>
            <li style="margin-bottom: 3px;"><strong>Pemimpin Upacara:</strong> Guru</li>
            <li style="margin-bottom: 3px;"><strong>Pembaca Teks Proklamasi:</strong> Guru Senior</li>
            <li style="margin-bottom: 3px;"><strong>Paduan Suara:</strong> Gabungan Bapak/Ibu Guru dari ke-3 Unit Sekolah</li>
            <li style="margin-bottom: 3px;"><strong>Perangkat Upacara Lainnya:</strong> Siswa</li>
        </ol>
    </li>

    <li style="margin-bottom: 10px;">
        <strong style="color: #991b1b; text-transform: uppercase;">RANGKAIAN ACARA SETELAH UPACARA BENDERA</strong>
        <ol style="list-style-type: decimal; padding-left: 20px; margin-top: 4px; line-height: 1.2;">
            <li style="margin-bottom: 3px;">Penampilan Atraksi Marchingband Perguruan PEMBDA Nias</li>
            <li style="margin-bottom: 3px;">Ramah Tamah & Syukuran Kemerdekaan</li>
            <li style="margin-bottom: 3px;">Sesi Foto Bersama Seluruh Keluarga Besar Perguruan PEMBDA Nias</li>
        </ol>
    </li>

    <li style="margin-bottom: 10px;">
        <strong style="color: #991b1b; text-transform: uppercase;">KEDISIPLINAN & PRESENSI DIGITAL PEMBDAHUB</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; line-height: 1.2;">
            <li style="margin-bottom: 3px;">Seluruh Guru & Pegawai <strong>WAJIB</strong> melakukan Presensi Masuk Upacara melalui sistem Presensi Digital PembdaHUB / TAP Kartu RFID di lokasi upacara.</li>
            <li style="margin-bottom: 3px;">Kepala Sekolah bertanggung jawab penuh atas rekapitulasi kehadiran fisik serta ketertiban barisan unit sekolah masing-masing.</li>
        </ul>
    </li>

    <li style="margin-bottom: 10px;">
        <strong style="color: #991b1b; text-transform: uppercase;">PERSIAPAN UNIT SEKOLAH (SMP, SMA, SMK)</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; line-height: 1.2;">
            <li style="margin-bottom: 3px;">Masing-masing Kepala Sekolah beserta seluruh jajaran unit sekolah diwajibkan untuk segera melakukan persiapan internal secara optimal (termasuk koordinasi gladi bersih Paskibraka, latihan Paduan Suara, latihan Marchingband, kerapian seragam siswa, serta kesiapan perangkat presensi digital).</li>
        </ul>
    </li>
</ol>

<p style="margin-top: 14px; line-height: 1.25;"><strong>PENUTUP:</strong><br>
Mengingat pentingnya acara ini sebagai wujud penghormatan, jiwa nasionalisme, dan rasa syukur atas kemerdekaan Bangsa Indonesia, kehadiran dan persiapan matang seluruh unit sekolah tepat pada waktunya sangat diharapkan. Atas perhatian, kehadiran, dan kerja sama yang baik, kami ucapkan terima kasih.</p>

<div style="margin-top: 14px; padding: 10px; text-align: center; border-top: 2px dashed #ef4444; border-bottom: 2px dashed #ef4444; background-color: #fafafa; border-radius: 6px; line-height: 1.2;">
    <p style="margin: 0; font-weight: bold; color: #b91c1c; font-size: 14px; text-transform: uppercase; letter-spacing: 0.08em;">
        DIRGAHAYU REPUBLIK INDONESIA KE-81
    </p>
    <p style="margin: 3px 0 0 0; font-weight: 800; color: #111827; font-size: 13px;">
        <em>MERDEKA! MERDEKA! MERDEKA!</em>
    </p>
</div>';

        $hash = FoundationLetter::generateHash($letterNumber, $title, $effectiveDate);

        // Find existing letter about HUT RI or with old letter numbers to UPDATE rather than create duplicates
        $existingLetter = FoundationLetter::where('title', 'like', '%Hari Kemerdekaan%')
            ->orWhere('title', 'like', '%HUT%RI%')
            ->orWhere('letter_number', '08/SE/YP-PEMBDA/VIII/2026')
            ->orWhere('letter_number', '06/SE/YP-PEMBDA/VIII/2026')
            ->first();

        $data = [
            'letter_number'      => $letterNumber,
            'title'              => $title,
            'category'           => 'undangan',
            'content'            => $content,
            'effective_date'     => $effectiveDate,
            'deadline_date'      => $deadlineDate,
            'recipients'         => [
                'target_keys'   => ['pengurus', 'kepala_sekolah', 'guru_pegawai', 'siswa'],
                'target_labels' => [
                    'Pengurus Yayasan Perguruan Pembda Nias',
                    'Kepala Sekolah se-Perguruan Pembda Nias',
                    'Bapak/Ibu Guru dan Pegawai se-Perguruan Pembda Nias',
                    'Siswa/Siswi se-Perguruan Pembda Nias'
                ],
                'unit_ids'      => [1, 2, 3],
            ],
            'signatory_name'     => $signatoryName,
            'signatory_position' => $signatoryPosition,
            'signature_hash'     => $hash,
            'signed_at'          => now(),
            'signed_by_user_id'  => $user ? $user->id : null,
            'status'             => 'published',
        ];

        if ($existingLetter) {
            $existingLetter->update($data);
            $targetId = $existingLetter->id;
        } else {
            $created = FoundationLetter::create($data);
            $targetId = $created->id;
        }

        // Cleanup any other duplicate HUT RI letters if exist
        FoundationLetter::where('id', '!=', $targetId)
            ->where(function($q) {
                $q->where('letter_number', '08/SE/YP-PEMBDA/VIII/2026')
                  ->orWhere('title', 'like', '%Hari Kemerdekaan%');
            })->delete();
    }
}
