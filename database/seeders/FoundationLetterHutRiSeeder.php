<?php

namespace Database\Seeders;

use App\Models\FoundationLetter;
use App\Models\User;
use Illuminate\Database\Seeder;

class FoundationLetterHutRiSeeder extends Seeder
{
    public function run(): void
    {
        $letterNumber = '08/SE/YP-PEMBDA/VIII/2026';
        $title = 'Pelaksanaan Peringatan Hari Kemerdekaan Republik Indonesia Ke-81 Tahun 2026 di Lingkungan Yayasan Perguruan Pembda Nias';
        $effectiveDate = '2026-08-10';
        $deadlineDate = '2026-08-17';

        $signatoryName = 'Yulianus Zega, S.Kom,M.Pd.T';
        $signatoryPosition = 'Ketua Yayasan Perguruan PEMBDA Nias';

        $user = User::where('role', 'superadmin')->first() ?? User::first();

        $content = '<p>Dengan hormat,</p>
<p>Dalam rangka menyambut dan memeriahkan <strong>Hari Ulang Tahun (HUT) Ke-81 Kemerdekaan Republik Indonesia Tahun 2026</strong> dengan tema nasional <em>"Nusantara Baru, Indonesia Maju"</em>, Pengurus Yayasan Perguruan PEMBDA Nias menyampaikan ketentuan pelaksanaan Peringatan HUT Ke-81 RI yang wajib dipedomani dan dilaksanakan oleh seluruh jajaran unit sekolah (SMP Swasta Pembda 2 Gunungsitoli, SMA Swasta Pembda 1 Gunungsitoli, dan SMK Swasta Pembda Nias):</p>

<ol style="margin-left: 20px; line-height: 1.8;">
    <li><strong>PELAKSANAAN UPACARA BENDERA PERINGATAN HUT KE-81 RI</strong>
        <ul>
            <li><strong>Hari/Tanggal:</strong> Senin, 17 Agustus 2026</li>
            <li><strong>Waktu:</strong> Pukul 07.30 WIB (Seluruh peserta upacara wajib hadir di lokasi paling lambat pukul 07.00 WIB)</li>
            <li><strong>Tempat:</strong> Lapangan Yayasan Perguruan Pembda Nias, Jl. Pelita No. 09 Gunungsitoli</li>
            <li><strong>Peserta Upacara:</strong> Seluruh Pengurus Yayasan, Kepala Sekolah, Bapak/Ibu Guru, Staf Pegawai, serta Seluruh Siswa/Siswi SMP, SMA, dan SMK Swasta Pembda Nias.</li>
            <li><strong>Ketentuan Pakaian:</strong>
                <ul>
                    <li><strong>Pengurus, Kepala Sekolah, Guru & Pegawai:</strong> Pakaian Nuansa Adat Nasional</li>
                    <li><strong>Siswa/Siswi:</strong> Seragam Lengkap dengan Atribut (Topi & Dasi) dan Sepatu Hitam</li>
                </ul>
            </li>
        </ul>
    </li>

    <li><strong>PETUGAS PELAKSANA UPACARA</strong>
        <ol type="1">
            <li><strong>Petugas dan Perangkat Upacara:</strong> Paskibraka Sekolah</li>
            <li><strong>Pembina Upacara:</strong> Ketua Yayasan</li>
            <li><strong>Pemimpin Upacara:</strong> Guru</li>
            <li><strong>Pembaca Teks Proklamasi:</strong> Guru Senior</li>
            <li><strong>Paduan Suara:</strong> Gabungan Bapak/Ibu Guru dari ke-3 Unit Sekolah</li>
        </ol>
    </li>

    <li><strong>KEGIATAN SETELAH UPACARA</strong>
        <ol type="1">
            <li>Penampilan Marchingband Perguruan Pembda Nias</li>
            <li>Ramah Tamah</li>
            <li>Foto Bersama</li>
        </ol>
    </li>

    <li><strong>KEDISIPLINAN & PRESENSI DIGITAL PEMBDAHUB</strong>
        <ul>
            <li>Seluruh Guru & Pegawai <strong>WAJIB</strong> melakukan Presensi Masuk Upacara melalui sistem Presensi Digital PembdaHUB / TAP Kartu RFID di lokasi upacara.</li>
            <li>Kepala Sekolah bertanggung jawab penuh atas rekapitulasi kehadiran fisik serta ketertiban barisan unit sekolah masing-masing.</li>
        </ul>
    </li>
</ol>

<p><strong>PENUTUP:</strong><br>
Demikian Surat Edaran ini disampaikan untuk dilaksanakan dengan penuh semangat nasionalisme, jiwa gotong royong, serta rasa tanggung jawab. Atas perhatian dan kerja sama Bapak/Ibu Kepala Sekolah, Guru, Pegawai, serta siswa/siswi sekalian, kami ucapkan terima kasih.</p>

<p><em>Merdeka! Merdeka! Merdeka!</em></p>';

        $hash = FoundationLetter::generateHash($letterNumber, $title, $effectiveDate);

        FoundationLetter::updateOrCreate(
            ['letter_number' => $letterNumber],
            [
                'title'              => $title,
                'category'           => 'edaran',
                'content'            => $content,
                'effective_date'     => $effectiveDate,
                'deadline_date'      => $deadlineDate,
                'recipients'         => [
                    'target_keys'   => ['kepala_sekolah', 'guru_pegawai', 'siswa', 'pengurus'],
                    'target_labels' => [
                        'Kepala Sekolah se-Perguruan Pembda Nias',
                        'Bapak/Ibu Guru dan Pegawai se-Perguruan Pembda Nias',
                        'Siswa/Siswi se-Perguruan Pembda Nias',
                        'Pengurus Yayasan Perguruan Pembda Nias'
                    ],
                    'unit_ids'      => [1, 2, 3],
                ],
                'signatory_name'     => $signatoryName,
                'signatory_position' => $signatoryPosition,
                'signature_hash'     => $hash,
                'signed_at'          => now(),
                'signed_by_user_id'  => $user ? $user->id : null,
                'status'             => 'published',
            ]
        );
    }
}
