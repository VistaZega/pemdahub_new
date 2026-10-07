<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionOption;
use App\Models\CbtExam;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NumerasiCompetitionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari Unit SMK Swasta Pembda Nias (sekolah aktif non-yayasan)
        $school = School::where('name', 'LIKE', '%SMK%')->first()
            ?? School::schoolsOnly()->first();

        if (!$school) {
            $this->command?->error('Sekolah SMK tidak ditemukan.');
            return;
        }

        // 2. Tahun Pelajaran Aktif & Semester Aktif (JANGAN DIHAPUS / MODIFIKASI)
        $academicYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::latest('id')->first();
        $semester = Semester::where('is_active', true)->first()
            ?? Semester::latest('id')->first();

        // 3. Mapel Matematika
        $subject = Subject::where('school_id', $school->id)
            ->where(function($q) {
                $q->where('name', 'LIKE', '%Matematika%')
                  ->orWhere('subject_name', 'LIKE', '%Matematika%');
            })->first()
            ?? Subject::where('school_id', $school->id)->first();

        if (!$subject) {
            $subject = Subject::create([
                'school_id' => $school->id,
                'name' => 'Matematika (Numerasi)',
                'code' => 'NUM-SMK',
                'is_active' => true,
            ]);
        }

        // 4. Guru Pengampu (ambil guru aktif di sekolah ini)
        $teacher = Teacher::where('school_id', $school->id)->where('is_active', true)->first();

        $bankName = 'Bank Soal Lomba Numerasi SMK Pembda 2026 (Industrial Math Challenge)';

        // 5. Buat atau perbarui Bank Soal
        $bank = CbtQuestionBank::updateOrCreate(
            [
                'school_id'        => $school->id,
                'bank_name'        => $bankName,
            ],
            [
                'subject_id'       => $subject->id,
                'teacher_id'       => $teacher?->id,
                'academic_year_id' => $academicYear?->id,
                'description'      => 'Kumpulan 40 soal numerasi terapan lomba Industrial Math Challenge: Hitung Tepat, Kerja Akurat.',
                'grade_level'      => '10',
                'total_questions'  => 40,
                'is_active'        => true,
                'is_shared'        => true,
            ]
        );

        // 40 Soal dari Simulasi
        $questionsData = [
            ["q" => "Sebuah sepeda motor memiliki diameter piston (bore) 50 mm dan panjang langkah (stroke) 50 mm. Berapa kapasitas silinder (displacement) mesin tersebut?", "o" => ["98 cc", "110 cc", "125 cc", "150 cc"], "a" => 0],
            ["q" => "Perbandingan gigi transmisi (gear ratio) antara gigi penggerak berjumlah 15 mata dan gigi yang digerakkan berjumlah 45 mata adalah...", "o" => ["1:2", "1:3", "3:1", "2:1"], "a" => 1],
            ["q" => "Hambatan total dari tiga buah resistor masing-masing 10 Ohm, 20 Ohm, dan 30 Ohm yang dirangkai secara seri adalah...", "o" => ["10 Ohm", "30 Ohm", "60 Ohm", "100 Ohm"], "a" => 2],
            ["q" => "Volume galian tanah untuk pondasi berbentuk trapesium dengan luas penampang melintang 0,8 m2 dan panjang galian 25 meter adalah...", "o" => ["15 m3", "20 m3", "25 m3", "30 m3"], "a" => 1],
            ["q" => "Sebuah file berukuran 750 Megabyte (MB) diunduh menggunakan koneksi internet dengan kecepatan stabil 10 Mbps. Berapa detik estimasi waktu unduh minimal?", "o" => ["300 detik", "450 detik", "600 detik", "750 detik"], "a" => 2],
            ["q" => "Jarak pengereman minimum sebuah kendaraan pada kecepatan 72 km/jam (20 m/s) dengan perlambatan konstan 5 m/s2 adalah...", "o" => ["20 meter", "30 meter", "40 meter", "50 meter"], "a" => 2],
            ["q" => "Biaya operasional bengkel per bulan adalah Rp 12.000.000. Jika rata-rata keuntungan bersih setiap unit servis motor adalah Rp 40.000, berapa unit motor yang harus diservis agar bengkel mencapai titik impas (BEP)?", "o" => ["200 unit", "250 unit", "300 unit", "350 unit"], "a" => 2],
            ["q" => "Pengecatan dinding seluas 120 m2 membutuhkan 2 lapisan cat (double coating). Berapa liter cat total yang diperlukan jika 1 liter mencakup 10 m2 untuk satu lapis?", "o" => ["12 liter", "18 liter", "24 liter", "30 liter"], "a" => 2],
            ["q" => "Daya semu pada rangkaian listrik AC tercatat 500 VA dengan faktor daya (power factor) 0,8. Berapa daya aktif (Watt) yang dihasilkan?", "o" => ["350 Watt", "400 Watt", "450 Watt", "500 Watt"], "a" => 1],
            ["q" => "Sebuah alamat IP dengan subnet mask /28 (255.255.255.240) memiliki berapa jumlah total IP address dalam satu blok subnet tersebut?", "o" => ["8 IP", "16 IP", "32 IP", "64 IP"], "a" => 1],
            ["q" => "Volume oli mesin pada karter awalnya 3,5 liter. Setelah pengurasan total, dituang oli baru sebanyak 3.200 ml. Berapa mililiter kekurangan oli dari volume standar awal?", "o" => ["150 ml", "200 ml", "250 ml", "300 ml"], "a" => 3],
            ["q" => "Sebuah roda gigi berputar dengan kecepatan 180 putaran per menit (rpm). Berapa derajat total putaran roda gigi tersebut dalam waktu 10 detik?", "o" => ["5.400°", "7.200°", "10.800°", "12.600°"], "a" => 2],
            ["q" => "Kotak kemasan komponen memiliki ukuran panjang 50 cm, lebar 40 cm, dan tinggi 30 cm. Berapa kapasitas volume kotak tersebut dalam satuan liter?", "o" => ["50 liter", "60 liter", "75 liter", "90 liter"], "a" => 1],
            ["q" => "Campuran beton mutu K-225 menggunakan perbandingan Semen : Pasir : Kerikil adalah 1 : 2 : 3. Jika total adukan yang dibuat sebanyak 600 kg, berapa kg berat pasir yang dibutuhkan?", "o" => ["100 kg", "150 kg", "200 kg", "300 kg"], "a" => 2],
            ["q" => "Tegangan pada jepit sumber arus adalah 12 Volt dan mengalirkan arus 400 mA melalui sebuah komponen. Berapa besar hambatan (Ohm) komponen tersebut?", "o" => ["15 Ohm", "20 Ohm", "25 Ohm", "30 Ohm"], "a" => 3],
            ["q" => "Hasil pengukuran presisi dari 5 sampel pelat besi (dalam mm) adalah: 12,1; 11,8; 12,0; 12,2; 11,9. Berapa nilai median dari data pengukuran tersebut?", "o" => ["11,9 mm", "12,0 mm", "12,1 mm", "12,2 mm"], "a" => 1],
            ["q" => "Keliling sebuah ban kendaraan adalah 200 cm (2 meter). Jika ban berputar sebanyak 5.000 kali putaran penuh, berapa kilometer jarak total yang ditempuh?", "o" => ["5 km", "8 km", "10 km", "12 km"], "a" => 2],
            ["q" => "Sebuah bengkel menjual produk rakitan seharga Rp 2.000.000 belum termasuk PPN 11%. Berapa total harga yang harus dibayar pembeli termasuk PPN?", "o" => ["Rp 2.110.000", "Rp 2.200.000", "Rp 2.220.000", "Rp 2.300.000"], "a" => 2],
            ["q" => "Suhu kerja optimal suatu mesin industri tercatat 176 °F. Berapa suhu tersebut jika dikonversi ke dalam skala Celsius (°C)?", "o" => ["70 °C", "75 °C", "80 °C", "85 °C"], "a" => 2],
            ["q" => "Sebuah switch jaringan memiliki 24 port aktif dengan konsumsi daya masing-masing 2,5 Watt. Berapa kWh total energi yang dikonsumsi jika switch menyala non-stop selama 30 hari?", "o" => ["36,0 kWh", "40,5 kWh", "43,2 kWh", "48,0 kWh"], "a" => 2],
            ["q" => "Sekring utama kelistrikan motor tertera rating 10A dan bekerja pada tegangan 12V. Jika terjadi lonjakan arus hingga 15A, berapa daya (Watt) aktual yang melewati sekring saat terjadi beban lebih?", "o" => ["120 Watt", "150 Watt", "180 Watt", "210 Watt"], "a" => 2],
            ["q" => "Luas permukaan tangki silinder tertutup dengan jari-jari alas 7 meter dan tinggi 10 meter adalah... (Gunakan π = 22/7)", "o" => ["616 m2", "748 m2", "880 m2", "924 m2"], "a" => 1],
            ["q" => "Sebuah conveyor belt bergerak dengan kecepatan konstan 1,5 meter per detik. Berapa waktu yang dibutuhkan material untuk berpindah sejauh 90 meter?", "o" => ["45 detik", "60 detik", "75 detik", "90 detik"], "a" => 1],
            ["q" => "Alat pres hidrolik dibeli seharga Rp 15.000.000 dengan umur ekonomis 5 tahun dan nilai sisa Rp 3.000.000. Berapa besar beban penyusutan per tahun menggunakan metode garis lurus?", "o" => ["Rp 2.000.000", "Rp 2.400.000", "Rp 3.000.000", "Rp 3.600.000"], "a" => 1],
            ["q" => "Berdasarkan hukum Pascal, jika piston kecil berluas 2 cm2 diberi gaya 10 N, dan piston besar berluas 20 cm2, berapa besar gaya angkat yang dihasilkan pada piston besar?", "o" => ["50 N", "100 N", "150 N", "200 N"], "a" => 1],
            ["q" => "Nilai rata-rata uji kompetensi 15 siswa TSM adalah 78. Jika digabungkan dengan 5 siswa susulan yang memiliki nilai rata-rata 86, berapa nilai rata-rata gabungan seluruh 20 siswa?", "o" => ["79,0", "79,5", "80,0", "80,5"], "a" => 2],
            ["q" => "Sebidang tanah pada denah berskala 1:500 berbentuk persegi panjang ukuran 8 cm × 5 cm. Berapa luas sebenarnya tanah tersebut dalam meter persegi?", "o" => ["800 m2", "900 m2", "1000 m2", "1250 m2"], "a" => 2],
            ["q" => "Sebuah ruang praktik memasang 4 lampu LED masing-masing 18 Watt dan 2 unit AC masing-masing 350 Watt menyala bersamaan. Berapa total daya aktif yang ditarik?", "o" => ["750 Watt", "772 Watt", "800 Watt", "850 Watt"], "a" => 1],
            ["q" => "Gir A (15 gigi) memutar Gir B (45 gigi), seporos dengan Gir B ada Gir C (20 gigi) memutar Gir D (60 gigi). Jika Gir A berputar 180 rpm, berapa kecepatan putaran Gir D?", "o" => ["10 rpm", "15 rpm", "20 rpm", "30 rpm"], "a" => 2],
            ["q" => "Generator diesel mengonsumsi solar 1,8 liter per jam. Jika dioperasikan selama 4 jam 30 menit, berapa total biaya solar jika harga Rp 6.800 per liter?", "o" => ["Rp 52.200", "Rp 55.080", "Rp 57.120", "Rp 61.200"], "a" => 1],
            ["q" => "Dinding bata sepanjang 10 m dan tinggi 3 m. Jika setiap 1 m2 butuh 70 bata merah, dan terdapat bukaan pintu/jendela seluas 3 m2, berapa jumlah bata merah bersih yang harus dibeli?", "o" => ["1750 buah", "1820 buah", "1890 buah", "2100 buah"], "a" => 2],
            ["q" => "Switch jaringan meneruskan data dengan kecepatan 100 Megabyte per detik. Berapa detik waktu untuk mentransfer data sebesar 12 Gigabyte (GB)?", "o" => ["100 detik", "115 detik", "122,88 detik", "150 detik"], "a" => 2],
            ["q" => "Mobil balap menghabiskan 45 liter bahan bakar untuk jarak 315 km. Berapa efisiensi konsumsi bahan bakar mobil tersebut dalam km/liter?", "o" => ["6 km/l", "6,5 km/l", "7 km/l", "7,5 km/l"], "a" => 2],
            ["q" => "Set alat ukur presisi berharga Rp 1.250.000 mendapat diskon bertingkat: 20% lalu diskon tambahan 10% dari harga setelah diskon pertama. Berapa harga akhir yang harus dibayar?", "o" => ["Rp 850.000", "Rp 900.000", "Rp 950.000", "Rp 1.000.000"], "a" => 1],
            ["q" => "Multitester menunjukkan tegangan drop sebesar 2,4 Volt pada kabel saat dialiri arus 3 Ampere. Berapa besar hambatan kabel tersebut?", "o" => ["0,5 Ohm", "0,8 Ohm", "1,2 Ohm", "1,5 Ohm"], "a" => 1],
            ["q" => "Poros baja silinder pejal berdiameter 40 mm dan panjang 500 mm. Jika berat jenis baja 7,8 g/cm3, berapa perkiraan berat poros baja tersebut dalam kg?", "o" => ["3,5 kg", "4,2 kg", "4,9 kg", "5,6 kg"], "a" => 2],
            ["q" => "Pengukuran waktu penyetelan karburator oleh 4 siswa mencatat: 12 menit, 15 menit, 10 menit, dan 19 menit. Berapa selisih waktu antara pengerjaan terlama dan tercepat?", "o" => ["7 menit", "8 menit", "9 menit", "10 menit"], "a" => 2],
            ["q" => "Mesin bubut otomatis memproduksi 120 komponen setiap 2 jam kerja. Berapa jam waktu yang dibutuhkan untuk memproduksi 540 komponen dengan kecepatan konstan?", "o" => ["7 jam", "8 jam", "9 jam", "10 jam"], "a" => 2],
            ["q" => "Pemasangan ubin lantai granit ukuran 60 cm × 60 cm pada ruang seluas 36 m2. Berapa jumlah lembar granit utuh minimal yang dibutuhkan?", "o" => ["90 lembar", "100 lembar", "110 lembar", "120 lembar"], "a" => 1],
            ["q" => "Panitia lomba menyediakan anggaran konsumsi Rp 1.500.000 untuk 50 orang. Jika telah terpakai Rp 350.000 untuk geladi bersih, berapa sisa anggaran rata-rata per orang untuk hari H?", "o" => ["Rp 20.000", "Rp 21.500", "Rp 23.000", "Rp 25.000"], "a" => 2],
        ];

        $labels = ['A', 'B', 'C', 'D'];

        foreach ($questionsData as $idx => $qData) {
            $correctLabel = $labels[$qData['a']];

            $question = CbtQuestion::updateOrCreate(
                [
                    'question_bank_id' => $bank->id,
                    'question_text'    => $qData['q'],
                ],
                [
                    'question_type'    => 'multiple_choice',
                    'points'           => 1,
                    'difficulty'       => 'sedang',
                    'topic'            => 'Numerasi Kejuruan',
                    'answer_key'       => $correctLabel,
                    'is_active'        => true,
                ]
            );

            // Bersihkan opsi lama jika ada lalu buat baru
            $question->options()->delete();

            foreach ($qData['o'] as $oIdx => $optText) {
                $label = $labels[$oIdx];
                CbtQuestionOption::create([
                    'question_id'   => $question->id,
                    'option_label'  => $label,
                    'option_text'   => $optText,
                    'is_correct'    => ($label === $correctLabel),
                    'sort_order'    => $oIdx + 1,
                ]);
            }
        }

        $bank->update(['total_questions' => count($questionsData)]);

        $this->command?->info("✔ Berhasil seeding 40 butir soal lomba numerasi ke Bank Soal ID: {$bank->id}");
    }
}
