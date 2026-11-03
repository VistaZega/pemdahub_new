<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CbtQuestionsTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CbtQuestionsDataSheet(),
            new CbtQuestionsGuideSheet(),
        ];
    }
}

/**
 * Sheet 1: Data soal (yang diisi pengguna)
 */
class CbtQuestionsDataSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Soal';
    }

    public function headings(): array
    {
        return [
            'question',
            'question_type',
            'correct_answer',
            'points',
            'difficulty',
            'topic',
            'explanation',
            'image_filename',
            'video_url',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Berapakah hasil dari $2 + 3 \times 4$?',
                'multiple_choice',
                'B',
                1,
                'sedang',
                'Aritmatika',
                'Operasi perkalian didahulukan: $3 \times 4 = 12$, lalu $2 + 12 = 14$',
                '',
                '',
                '20',
                '14',
                '18',
                '24',
                '',
            ],
            [
                'Jika $F = m \cdot a$, dan massa benda $m = 5$ kg serta percepatan $a = 3$ m/s², maka gaya $F$ adalah...',
                'multiple_choice',
                'C',
                2,
                'sedang',
                'Hukum Newton',
                '$F = m \cdot a = 5 \times 3 = 15$ Newton',
                'soal_fisika.jpg',
                '',
                '10 N',
                '12 N',
                '15 N',
                '20 N',
                '25 N',
            ],
            [
                'Reaksi kimia $2H_2 + O_2 \rightarrow 2H_2O$ adalah reaksi pembentukan air. Apakah benar reaksi tersebut memerlukan 2 mol oksigen?',
                'true_false',
                'false',
                1,
                'sedang',
                'Reaksi Kimia',
                'Reaksi tersebut memerlukan 1 mol $O_2$ (bukan 2 mol), karena koefisien $O_2$ adalah 1',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'Tentukan nilai $x$ dari persamaan kuadrat $x^2 - 5x + 6 = 0$ dan jelaskan langkah penyelesaiannya!',
                'essay',
                'x = 2 atau x = 3. Faktorkan: $(x-2)(x-3) = 0$',
                3,
                'sulit',
                'Persamaan Kuadrat',
                'Faktorisasi: $x^2 - 5x + 6 = (x-2)(x-3) = 0$, maka $x = 2$ atau $x = 3$',
                '',
                'https://www.youtube.com/watch?v=contoh123',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'Rumus luas lingkaran adalah $L = \pi r^2$. Jika $r = 7$ cm, maka luas lingkaran = ... cm²',
                'fill_blank',
                '154',
                1,
                'mudah',
                'Geometri',
                '$L = \pi r^2 = \frac{22}{7} \times 7^2 = \frac{22}{7} \times 49 = 154$ cm²',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'Perhatikan grafik di bawah ini. Manakah grafik yang menunjukkan fungsi trigonometri $y = \cos(x)$?',
                'multiple_choice',
                'A',
                2,
                'sedang',
                'Trigonometri Bergambar',
                'Grafik A menunjukkan kurva kosinus yang dimulai dari titik puncak (0, 1).',
                '',
                '',
                'opsi_a.png',
                'opsi_b.png',
                'opsi_c.png',
                'opsi_d.png',
                '',
            ],
            [
                'Tulislah kalimat thayyibah (lafadz basmalah) atau tulislah salam dalam bahasa Jepang (Konnichiwa) menggunakan huruf aslinya!',
                'essay',
                'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ / こんにちは',
                5,
                'sedang',
                'Agama & Bahasa',
                'Siswa dapat mengetik teks atau menulis manual dengan jari/stylus di lembar kanvas tulisan tangan CBT.',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->array()) + 1;
        $lastCol = 'N'; // Now 14 columns (A-N)

        // Header styling
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7C3AED'], // Violet theme
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '5B21B6'],
                ],
            ],
        ]);

        // Data rows styling (contoh baris — italic abu-abu agar jelas ini contoh)
        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F3FF'], // Light violet background
            ],
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '6B7280'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Set column widths for better readability
        $sheet->getColumnDimension('A')->setWidth(60); // question
        $sheet->getColumnDimension('B')->setWidth(18); // question_type
        $sheet->getColumnDimension('C')->setWidth(18); // correct_answer
        $sheet->getColumnDimension('D')->setWidth(10); // points
        $sheet->getColumnDimension('E')->setWidth(12); // difficulty
        $sheet->getColumnDimension('F')->setWidth(22); // topic
        $sheet->getColumnDimension('G')->setWidth(45); // explanation
        $sheet->getColumnDimension('H')->setWidth(22); // image_filename
        $sheet->getColumnDimension('I')->setWidth(35); // video_url
        $sheet->getColumnDimension('J')->setWidth(25); // option_a
        $sheet->getColumnDimension('K')->setWidth(25); // option_b
        $sheet->getColumnDimension('L')->setWidth(25); // option_c
        $sheet->getColumnDimension('M')->setWidth(25); // option_d
        $sheet->getColumnDimension('N')->setWidth(25); // option_e

        // Add comments to header cells
        $sheet->getComment('A1')->getText()->createTextRun("Teks pertanyaan (WAJIB).\nMendukung LaTeX: gunakan $...$ untuk formula.\nContoh: \$x^2 + y^2 = r^2\$");
        $sheet->getComment('B1')->getText()->createTextRun("Tipe soal (WAJIB):\n- multiple_choice\n- true_false\n- essay\n- fill_blank");
        $sheet->getComment('C1')->getText()->createTextRun("Jawaban benar:\n- MC: A/B/C/D/E\n- True/False: true/false\n- Essay: kunci jawaban (referensi)\n- Fill blank: jawaban isian");
        $sheet->getComment('D1')->getText()->createTextRun('Bobot nilai soal. Default: 1');
        $sheet->getComment('E1')->getText()->createTextRun("Tingkat kesulitan:\n- mudah\n- sedang (default)\n- sulit");
        $sheet->getComment('H1')->getText()->createTextRun("Nama file gambar di folder images/\ndalam ZIP. Contoh: soal1.jpg\n\nKosongkan jika upload Excel tanpa gambar.");
        $sheet->getComment('I1')->getText()->createTextRun("URL video (YouTube, dll).\nContoh: https://youtube.com/watch?v=xxx\n\nKosongkan jika tidak ada video.");
        $sheet->getComment('J1')->getText()->createTextRun("Opsi A (WAJIB untuk MC).\nBisa berupa teks ATAU nama file gambar (contoh: opsi_a.png).\nJika berupa gambar, masukkan file gambarnya ke folder images/ dalam file .zip.");

        // Wrap text for long columns
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setWrapText(true);

        return [];
    }
}

/**
 * Sheet 2: Panduan pengisian lengkap
 */
class CbtQuestionsGuideSheet implements FromArray, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Panduan';
    }

    public function array(): array
    {
        return [
            ['PANDUAN PENGISIAN TEMPLATE SOAL CBT'],
            [''],
            ['═══ KOLOM-KOLOM TEMPLATE ═══'],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['question', 'Teks pertanyaan/soal (mendukung LaTeX)', 'YA', 'Berapakah $2 + 3 \times 4$?'],
            ['question_type', 'Tipe soal', 'YA', 'multiple_choice / true_false / essay / fill_blank'],
            ['correct_answer', 'Jawaban benar', 'YA*', 'B (MC), true/false (TF), teks (essay/fill)'],
            ['points', 'Bobot nilai soal', 'TIDAK (default: 1)', '1, 2, 5, 10'],
            ['difficulty', 'Tingkat kesulitan', 'TIDAK (default: sedang)', 'mudah / sedang / sulit'],
            ['topic', 'Topik atau bab', 'TIDAK', 'Aljabar, Sejarah Kemerdekaan'],
            ['explanation', 'Pembahasan jawaban (mendukung LaTeX)', 'TIDAK', 'Penjelasan jawaban benar'],
            ['image_filename', 'Nama file gambar dalam folder images/', 'TIDAK', 'soal1.jpg, diagram.png'],
            ['video_url', 'URL video YouTube atau link langsung', 'TIDAK', 'https://youtube.com/watch?v=xxx'],
            ['option_a - option_e', 'Opsi pilihan ganda: teks/LaTeX ATAU nama gambar', 'YA (MC)', 'Teks biasa, formula $x=2$, atau nama file opsi_a.png'],
            [''],
            ['═══ PANDUAN OPSI JAWABAN BERGAMBAR ═══'],
            ['Jika pilihan jawaban A, B, C, D, atau E berupa gambar:'],
            ['1. Cukup tulis nama file gambar di kolom option_a sampai option_e (contoh: opsi_a.png atau grafik1.jpg).'],
            ['2. Masukkan file-file gambar tersebut ke dalam folder images/ di dalam file ZIP yang diunggah.'],
            ['3. Saat ujian CBT, sistem akan otomatis menampilkan gambar pada tombol pilihan jawaban siswa.'],
            [''],
            ['═══ PANDUAN SOAL ESAI TULIS TANGAN (ARAB / JEPANG / MULOK) ═══'],
            ['Untuk soal esai yang membutuhkan tulisan tangan (seperti huruf Hijaiyah/Arab pada Agama Islam, atau Hiragana/Katakana/Kanji pada Bahasa Jepang):'],
            ['1. Pilih question_type = "essay".'],
            ['2. Di aplikasi CBT siswa, disediakan kanvas "Tulis Tangan" khusus dengan pena digital halus.'],
            ['3. Siswa dapat mencoret/menulis manual menggunakan sentuhan jari pada HP/tablet atau mouse komputer layaknya tanda tangan digital.'],
            ['4. Guru dapat melihat hasil goresan tulisan tangan siswa secara utuh saat melakukan koreksi esai.'],
            [''],
            ['═══ FORMAT UPLOAD ═══'],
            ['Mode', 'Kapan Digunakan', 'Format', 'Keterangan'],
            ['Excel saja', 'Soal tanpa gambar', '.xlsx', 'Upload langsung file Excel ini'],
            ['ZIP + Excel', 'Soal dengan gambar', '.zip', 'Buat ZIP berisi file Excel + folder images/'],
            [''],
            ['Struktur ZIP:', '', '', ''],
            ['  soal_import.zip', '', '', ''],
            ['  ├── soal.xlsx', '', '', '(file Excel ini)'],
            ['  └── images/', '', '', '(folder berisi gambar-gambar)'],
            ['      ├── soal1.jpg', '', '', ''],
            ['      ├── diagram.png', '', '', ''],
            ['      └── grafik.jpg', '', '', ''],
            [''],
            ['═══ PANDUAN FORMULA LaTeX ═══'],
            ['Penulisan', 'Hasil', 'Kategori', 'Keterangan'],
            ['$x^2$', 'x²', 'Matematika', 'Pangkat'],
            ['$\sqrt{x}$', '√x', 'Matematika', 'Akar kuadrat'],
            ['$\frac{a}{b}$', 'a/b', 'Matematika', 'Pecahan'],
            ['$\pi$', 'π', 'Matematika', 'Pi'],
            ['$\sum_{i=1}^{n} i$', 'Σi', 'Matematika', 'Sigma/penjumlahan'],
            ['$\int_0^1 x\,dx$', '∫x dx', 'Matematika', 'Integral'],
            ['$\lim_{x \to 0}$', 'lim x→0', 'Matematika', 'Limit'],
            ['$H_2O$', 'H₂O', 'Kimia', 'Rumus kimia (subscript)'],
            ['$CO_2$', 'CO₂', 'Kimia', 'Karbon dioksida'],
            ['$2H_2 + O_2 \rightarrow 2H_2O$', 'Reaksi', 'Kimia', 'Persamaan reaksi'],
            ['$F = m \cdot a$', 'F = m·a', 'Fisika', 'Hukum Newton II'],
            ['$E = mc^2$', 'E = mc²', 'Fisika', 'Energi (Einstein)'],
            ['$v = \frac{s}{t}$', 'v = s/t', 'Fisika', 'Kecepatan'],
            ['$\alpha, \beta, \gamma, \theta$', 'α, β, γ, θ', 'Simbol', 'Huruf Yunani'],
            ['$\leq, \geq, \neq, \approx$', '≤, ≥, ≠, ≈', 'Simbol', 'Perbandingan'],
            ['$\pm, \times, \div, \infty$', '±, ×, ÷, ∞', 'Simbol', 'Operator'],
            ['$\vec{F}$', 'F (vektor)', 'Fisika', 'Vektor'],
            ['$\overline{AB}$', 'AB (garis)', 'Geometri', 'Garis'],
            ['$\angle ABC$', '∠ABC', 'Geometri', 'Sudut'],
            ['$^{14}_{6}C$', '¹⁴₆C', 'Kimia', 'Isotop'],
            [''],
            ['═══ ATURAN PENTING ═══'],
            ['1. Jangan mengubah nama kolom di baris pertama sheet "Soal"'],
            ['2. Baris contoh (font miring abu-abu) boleh dihapus atau ditimpa dengan data Anda'],
            ['3. Untuk soal True/False, kosongkan semua option_a sampai option_e'],
            ['4. Untuk soal Essay, correct_answer berisi kunci jawaban sebagai referensi penilaian guru'],
            ['5. Baris kosong akan otomatis dilewati saat import'],
            ['6. Minimal harus ada 1 baris soal yang valid'],
            ['7. Formula LaTeX diapit tanda dolar: $rumus$ (inline) atau $$rumus$$ (block)'],
            ['8. Gambar yang didukung: .jpg, .jpeg, .png, .gif, .webp, .svg (maks 5MB per file)'],
            ['9. Video bisa berupa URL YouTube atau link langsung ke file video'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '7C3AED']],
        ]);
        $sheet->mergeCells('A1:D1');

        // Section headers styling
        foreach ([3, 16, 28, 49] as $row) {
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '7C3AED']],
            ]);
            $sheet->mergeCells("A{$row}:D{$row}");
        }

        // Table headers (Kolom info)
        foreach ([4, 17, 29] as $headerRow) {
            $sheet->getStyle("A{$headerRow}:D{$headerRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7C3AED'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(50);

        // Aturan penting title
        $sheet->getStyle('A49')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'DC2626']],
        ]);

        return [];
    }
}
