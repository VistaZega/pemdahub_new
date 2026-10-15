<?php

namespace Database\Seeders;

use App\Models\FoundationLetter;
use App\Models\User;
use Illuminate\Database\Seeder;

class FoundationLetterKabutAsapSeeder extends Seeder
{
    public function run(): void
    {
        $letterNumber = '07/SE/YP-PEMBDA/X/2026';
        $title = 'Pemberlakuan Pembelajaran Jarak Jauh (PJJ) Berbasis LMS PembdaHUB dalam Rangka Antisipasi Dampak Bencana Kabut Asap di Lingkungan Yayasan Perguruan Pembda Nias';
        $effectiveDate = '2026-10-15';
        $deadlineDate = '2026-10-21';

        $signatoryName = 'Yulianus Zega, S.Kom, M.Pd.T';
        $signatoryPosition = 'Ketua Yayasan Perguruan PEMBDA Nias';

        $user = User::where('role', 'superadmin')->first() 
            ?? User::where('username', 'yulzega')->first() 
            ?? User::first();

        $content = '<div style="border-left: 4px solid #f59e0b; padding-left: 14px; margin-bottom: 16px; background-color: #fffbeb; padding-top: 10px; padding-bottom: 10px; border-radius: 0 8px 8px 0; line-height: 1.35;">
    <p style="margin: 0; font-weight: bold; color: #b45309; text-transform: uppercase; font-size: 13px; letter-spacing: 0.05em;">
        <i class="fas fa-shield-virus"></i> TANGGAP DARURAT KESEHATAN & ANTISIPASI DAMPAK BENCANA KABUT ASAP
    </p>
    <p style="margin: 3px 0 0 0; color: #78350f; font-size: 12px; font-weight: 500;">
        Rujukan: Surat Kadisdik Prov. Sumut No. 400.3/106/DISDIK/2026 & Surat Kacabdisdik Wil. XIII No. 400.3/1729/CABDISDIK WIL. XIII/2026
    </p>
</div>

<p style="line-height: 1.35; margin-bottom: 8px;">Dengan hormat,</p>
<p style="line-height: 1.35; margin-bottom: 12px; text-align: justify;">
Menyikapi peningkatan intensitas kabut asap di wilayah Provinsi Sumatera Utara, khususnya di wilayah Kepulauan Nias dan Kota Gunungsitoli yang berpotensi menimbulkan gangguan kesehatan organ pernapasan (ISPA) serta menurunkan kualitas udara lingkungan belajar, serta sebagai tindak lanjut resmi atas <strong>Surat Kepala Dinas Pendidikan Provinsi Sumatera Utara Nomor: 400.3/106/DISDIK/2026</strong> dan <strong>Surat Kepala Cabang Dinas Pendidikan Wilayah XIII Nomor: 400.3/1729/CABDISDIK WIL. XIII/2026</strong> perihal <em>Antisipasi Dampak Kabut Asap di Satuan Pendidikan</em>, maka Pengurus Yayasan Perguruan PEMBDA Nias dengan ini menetapkan instruksi dan pedoman penyesuaian kegiatan belajar sebagai berikut:
</p>

<ol style="list-style-type: decimal; padding-left: 24px; margin-top: 10px; margin-bottom: 15px; line-height: 1.35;">
    <li style="margin-bottom: 12px;">
        <strong style="color: #92400e; text-transform: uppercase;">PEMBERLAKUAN SISTEM PEMBELAJARAN JARAK JAUH (PJJ) KHUSUS JENJANG SMA & SMK</strong>
        <p style="margin-top: 4px; text-align: justify;">
        Terhitung mulai tanggal <strong>15 s.d. 21 Oktober 2026</strong>, Kegiatan Belajar Mengajar (KBM) tatap muka di ruang kelas dialihkan ke <strong>Sistem Pembelajaran Jarak Jauh (PJJ) / Belajar Dari Rumah (BDR)</strong> yang <strong>diberlakukan khusus untuk unit SMA Swasta Pembda 1 Gunungsitoli dan SMKS Swasta Pembda Nias</strong> demi mengutamakan keselamatan dan perlindungan kesehatan peserta didik serta guru dari risiko paparan kabut asap tebal.
        </p>
    </li>

    <li style="margin-bottom: 12px;">
        <strong style="color: #92400e; text-transform: uppercase;">STATUS KHUSUS SMPS PEMBDA 2 GUNUNGSITOLI MENUNGGU PETUNJUK RESMI</strong>
        <p style="margin-top: 4px; text-align: justify;">
        Berhubung hingga saat ini belum ada petunjuk teknis atau surat edaran resmi dari instansi berwenang di tingkat daerah (Pemerintah Kota / Dinas Pendidikan Kota Gunungsitoli) bagi satuan pendidikan jenjang SMP, maka <strong>khusus untuk unit SMP Swasta Pembda 2 Gunungsitoli pelaksanaan PJJ menunggu petunjuk dan arahan lebih lanjut dari pihak yang berwenang</strong>. Untuk sementara, KBM di SMPS Pembda 2 tetap berjalan dengan mengedepankan prinsip kehati-hatian, pembatasan aktivitas fisik di luar kelas, pengetatan protokol kesehatan, serta kesiapsiagaan seluruh guru dan siswa memanfaatkan LMS PembdaHUB apabila sewaktu-waktu diterbitkan instruksi PJJ.
        </p>
    </li>

    <li style="margin-bottom: 12px;">
        <strong style="color: #92400e; text-transform: uppercase;">PELAKSANAAN PJJ BERBASIS LMS DIGITAL PADA APLIKASI PEMBDAHUB</strong>
        <p style="margin-top: 4px; text-align: justify;">
        Sistem Pembelajaran Jarak Jauh (PJJ) yang diberlakukan bagi unit sekolah di bawah naungan Yayasan Perguruan PEMBDA Nias dilaksanakan secara terpadu menggunakan platform <strong>Learning Management System (LMS) pada aplikasi digital PembdaHUB</strong> (<a href="https://perguruanpembda.com" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: bold;">perguruanpembda.com</a>):
        </p>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; line-height: 1.3;">
            <li style="margin-bottom: 3px;"><strong>Bapak/Ibu Guru:</strong> Mengunggah materi ajar digital, modul KBM, tugas terstruktur, serta kuis/evaluasi daring ke menu Course LMS masing-masing mapel.</li>
            <li style="margin-bottom: 3px;"><strong>Peserta Didik:</strong> Wajib login menggunakan akun PembdaHUB untuk mengakses bahan ajar, menyimak arahan guru, dan menyelesaikan penugasan harian.</li>
            <li style="margin-bottom: 3px;"><strong>Rekapitulasi Presensi:</strong> Kehadiran siswa tercatat secara sistematis dan otomatis melalui interaksi pada LMS PembdaHUB.</li>
        </ul>
    </li>

    <li style="margin-bottom: 12px;">
        <strong style="color: #92400e; text-transform: uppercase;">OTONOMI & KEWENANGAN TEKNIS PENERAPAN PJJ OLEH UNIT SEKOLAH</strong>
        <p style="margin-top: 4px; text-align: justify;">
        Teknis penerapan dan mekanisme operasional PJJ menggunakan LMS PembdaHUB merupakan <strong>kewenangan penuh masing-masing pimpinan unit sekolah</strong> (Kepala Sekolah beserta jajaran Dewan Guru), termasuk penyesuaian jadwal harian, proporsi pembelajaran sinkron/asinkron, serta jalur koordinasi wali kelas dengan orang tua/wali siswa.
        </p>
    </li>

    <li style="margin-bottom: 12px;">
        <strong style="color: #92400e; text-transform: uppercase;">PROTOKOL KESEHATAN, MITIGASI MANDIRI & PENGAWASAN BERKELANJUTAN</strong>
        <ul style="list-style-type: disc; padding-left: 20px; margin-top: 4px; line-height: 1.3;">
            <li style="margin-bottom: 3px;">Seluruh sivitas akademika diimbau untuk membatasi aktivitas fisik di luar ruangan (<em>outdoor</em>), selalu mengenakan masker medis/filtrasi partikel debu saat berada di luar rumah, menjaga hidrasi tubuh, dan segera memeriksakan diri bila mengalami gangguan pernapasan.</li>
            <li style="margin-bottom: 3px;">Langkah antisipasi dampak kabut asap selanjutnya serta penentuan operasional KBM setelah tanggal 21 Oktober 2026 akan dievaluasi dengan <strong>menunggu petunjuk dan arahan resmi lebih lanjut dari pihak yang berwenang</strong>.</li>
        </ul>
    </li>
</ol>

<p style="margin-top: 14px; line-height: 1.35; text-align: justify;">
<strong>PENUTUP:</strong><br>
Demikian Surat Edaran ini disampaikan untuk menjadi pedoman bersama dalam rangka antisipasi dampak bencana kabut asap di wilayah Provinsi Sumatera Utara, khususnya di lingkungan Yayasan Perguruan PEMBDA Nias. Atas perhatian, kerja sama, dan dedikasi Bapak/Ibu Kepala Sekolah, Guru, Pegawai, serta kerja sama seluruh Siswa dan Orang Tua, kami ucapkan terima kasih.
</p>

<div style="margin-top: 14px; padding: 10px; text-align: center; border-top: 2px dashed #f59e0b; border-bottom: 2px dashed #f59e0b; background-color: #fafafa; border-radius: 6px; line-height: 1.25;">
    <p style="margin: 0; font-weight: bold; color: #b45309; font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em;">
        YAYASAN PERGURUAN PEMBANGUNAN DAERAH NIAS (PEMBDA)
    </p>
    <p style="margin: 3px 0 0 0; font-weight: 800; color: #111827; font-size: 12px;">
        <em>"Maju Terus Menjaga Kesehatan Generasi & Mutu Pembelajaran Digital PembdaHUB!"</em>
    </p>
</div>';

        $hash = FoundationLetter::generateHash($letterNumber, $title, $effectiveDate);

        // Find existing letter about kabut asap to update rather than duplicate
        $existingLetter = FoundationLetter::where('title', 'like', '%Kabut Asap%')
            ->orWhere('title', 'like', '%PJJ%')
            ->orWhere('letter_number', $letterNumber)
            ->first();

        $data = [
            'letter_number'      => $letterNumber,
            'title'              => $title,
            'category'           => 'edaran',
            'content'            => $content,
            'effective_date'     => $effectiveDate,
            'deadline_date'      => $deadlineDate,
            'recipients'         => [
                'target_keys'   => ['kepala_sekolah', 'guru_pegawai', 'siswa'],
                'target_labels' => [
                    'Kepala Sekolah se-Perguruan Pembda Nias',
                    'Bapak/Ibu Guru dan Pegawai se-Perguruan Pembda Nias',
                    'Siswa/Siswi se-Perguruan Pembda Nias'
                ],
                'unit_ids'      => [1, 2, 3],
            ],
            'signatory_name'     => $signatoryName,
            'signatory_position' => $signatoryPosition,
            'signature_hash'     => $hash,
            'signed_at'          => '2026-10-15 08:00:00',
            'signed_by_user_id'  => $user ? $user->id : null,
            'status'             => 'published',
        ];

        if ($existingLetter) {
            // Keep existing signature_hash so shared URLs remain valid
            $data['signature_hash'] = $existingLetter->signature_hash;
            $existingLetter->update($data);
        } else {
            FoundationLetter::create($data);
        }
    }
}
