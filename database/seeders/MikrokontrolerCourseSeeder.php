<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\LmsCourse;
use App\Models\LmsClass;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class MikrokontrolerCourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Guru Yulianus Zega
        $userGuru = User::where('name', 'LIKE', '%Yulianus%')->first();
        if (!$userGuru) return;

        $teacher = Teacher::where('user_id', $userGuru->id)->first();
        if (!$teacher) {
            $teacher = Teacher::create([
                'user_id' => $userGuru->id,
                'teacher_number' => '198501012024011001',
                'name' => 'Yulianus Zega, S.Kom, M.Pd',
                'school_id' => 1,
            ]);
        }

        // 2. Mata Pelajaran & Kelas
        $subject = Subject::where('id', 219)->orWhere('subject_code', 'TE')->first();
        if (!$subject) {
            $subject = Subject::create([
                'subject_name' => 'Kosentrasi Keahlian TE',
                'subject_code' => 'TE',
                'school_id' => $teacher->school_id ?? 1
            ]);
        }

        $classroom = Classroom::where('class_name', 'X TAV')->first();
        if (!$classroom) {
            $classroom = Classroom::create([
                'class_name' => 'X TAV',
                'class_code' => 'XTAV',
                'grade_level' => 10,
                'school_id' => $teacher->school_id ?? 1,
                'academic_year_id' => 5,
            ]);
        }

        $academicYear = AcademicYear::where('is_active', 1)->first() ?? AcademicYear::first();
        $semester = Semester::where('is_active', 1)->first() ?? Semester::first();

        // 3. Course
        $course = LmsCourse::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'course_name' => 'Bahasa Pemrograman Mikrokontroler',
            ],
            [
                'school_id' => $teacher->school_id ?? 1,
                'subject_id' => $subject->id,
                'classroom_id' => $classroom->id,
                'code' => 'LMS-MIKRO-XTAV',
                'semester_id' => $semester->id ?? 7,
                'description' => 'Mata Pelajaran Konsentrasi Keahlian Teknik Elektronika (TE) untuk Kelas X TAV. Mempelajari arsitektur mikrokontroler AVR/ESP32, bahasa pemrograman C/C++ Embedded, manipulasi register digital I/O, antarmuka sensor & aktuator, relay driver, multiplexing display, serta penerapan otomasi industri.',
                'is_published' => true,
                'status' => 'active',
                'is_sequential' => false,
            ]
        );

        LmsClass::firstOrCreate(
            ['course_id' => $course->id, 'classroom_id' => $classroom->id],
            ['status' => 'active']
        );

        // 4. Modul
        $module = LmsModule::updateOrCreate(
            [
                'course_id' => $course->id,
                'sequence' => 1,
            ],
            [
                'title' => 'Modul 1: Pengenalan Mikrokontroler & Output Digital',
                'description' => 'Memahami arsitektur internal mikrokontroler (CPU 8-bit/32-bit), alokasi memori (Flash, SRAM, EEPROM), manipulasi register I/O (DDR, PORT, PIN), teknik penanganan arus Sinking/Sourcing, serta driver Relay & Display Seven-Segment.',
                'color' => 'emerald',
                'is_active' => true,
            ]
        );

        // 5. PDF Generation
        $pdfDir = storage_path('app/public/lms_materials');
        if (!File::exists($pdfDir)) File::makeDirectory($pdfDir, 0755, true);
        $publicPdfDir = public_path('storage/lms_materials');
        if (!File::exists($publicPdfDir)) File::makeDirectory($publicPdfDir, 0755, true);

        $pdfFileName = 'Modul_Pengenalan_Mikrokontroler_dan_Output_Digital_XTAV.pdf';
        $pdfPath = $pdfDir . '/' . $pdfFileName;
        $publicPdfPath = $publicPdfDir . '/' . $pdfFileName;

        $htmlPdf = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Modul 1 - Pemrograman Mikrokontroler</title>
            <style>
                body { font-family: sans-serif; color: #1e293b; line-height: 1.6; margin: 20px; font-size: 11pt; }
                .header { text-align: center; border-bottom: 3px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
                .header h1 { font-size: 18pt; margin: 0; color: #090d16; text-transform: uppercase; }
                .header h2 { font-size: 13pt; margin: 5px 0 0 0; color: #0284c7; }
                .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; background: #f8fafc; }
                .meta-table td { padding: 8px 12px; border: 1px solid #cbd5e1; }
                .meta-table td.label { font-weight: bold; background: #e2e8f0; width: 25%; }
                h3 { font-size: 13pt; color: #0f172a; border-left: 5px solid #059669; padding-left: 10px; margin-top: 25px; text-transform: uppercase; }
                p { margin-bottom: 10px; text-align: justify; }
                .code-box { font-family: monospace; background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 6px; font-size: 9.5pt; margin: 12px 0; word-wrap: break-word; }
                table.data-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9.5pt; }
                table.data-table th, table.data-table td { border: 1px solid #94a3b8; padding: 8px; text-align: left; }
                table.data-table th { background-color: #0f172a; color: #ffffff; text-transform: uppercase; }
                .info-box { background: #eff6ff; border: 1px solid #93c5fd; padding: 12px; border-radius: 6px; margin: 15px 0; font-size: 10pt; }
                .footer { text-align: center; font-size: 8pt; color: #64748b; margin-top: 40px; border-top: 1px solid #cbd5e1; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SMK PERGURUAN PEMBDA NIKEL</h1>
                <h2>KONSENTRASI KEAHLIAN TEKNIK ELEKTRONIKA (TE)</h2>
                <p style="margin: 3px 0 0 0; font-size: 9pt; color: #475569;">Buku Ajar Modul Pembelajaran Digital LMS PembdaHUB</p>
            </div>

            <table class="meta-table">
                <tr>
                    <td class="label">Mata Pelajaran:</td>
                    <td>Bahasa Pemrograman Mikrokontroler</td>
                    <td class="label">Guru Pengampu:</td>
                    <td>Yulianus Zega, S.Kom, M.Pd</td>
                </tr>
                <tr>
                    <td class="label">Konsentrasi Keahlian:</td>
                    <td>Teknik Elektronika (TE)</td>
                    <td class="label">Tingkat / Kelas:</td>
                    <td>X TAV (Teknik Audio Video)</td>
                </tr>
                <tr>
                    <td class="label">Judul Modul:</td>
                    <td colspan="3"><strong>Modul 1: Pengenalan Mikrokontroler & Output Digital</strong></td>
                </tr>
            </table>

            <h3>1. PENDAHULUAN & ARSITEKTUR MIKROKONTROLER</h3>
            <p>Mikrokontroler adalah sebuah sistem komputer yang seluruh komponen utamanya (CPU, Flash Program, SRAM, EEPROM, GPIO) terintegrasi dalam satu chip tunggal (Single Chip Microcomputer).</p>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Komponen Internal</th>
                        <th>Fungsi Utama</th>
                        <th>Spesifikasi Standar (ATmega328P / AVR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Flash Memory</strong></td>
                        <td>Menyimpan kode program C/C++ (.hex)</td>
                        <td>32 KB (Non-Volatile)</td>
                    </tr>
                    <tr>
                        <td><strong>SRAM (Static RAM)</strong></td>
                        <td>Menyimpan variabel sementara saat runtime</td>
                        <td>2 KB (Volatile)</td>
                    </tr>
                    <tr>
                        <td><strong>EEPROM</strong></td>
                        <td>Menyimpan data konfigurasi permanen</td>
                        <td>1 KB (Non-Volatile)</td>
                    </tr>
                </tbody>
            </table>

            <h3>2. REGISTER MANIPULASI DIGITAL I/O (AVR ARCHITECTURE)</h3>
            <p>Pengendalian pin GPIO dilakukan melalui manipulasi bit pada tiga register utama:</p>
            <ul>
                <li><strong>DDRx:</strong> Menentukan arah pin (1 = Output, 0 = Input).</li>
                <li><strong>PORTx:</strong> Menentukan level tegangan output (1 = HIGH / 5V, 0 = LOW / 0V).</li>
                <li><strong>PINx:</strong> Membaca nilai logika nyata pada pin fisik (Read-Only).</li>
            </ul>

            <div class="code-box">
// KODE C DIRECT REGISTER MANIPULATION
#include &lt;avr/io.h&gt;
#include &lt;util/delay.h&gt;

int main(void) {
    DDRB |= (1 &lt;&lt; PB5); // PB5 sebagai OUTPUT
    while(1) {
        PORTB |= (1 &lt;&lt; PB5);  // HIGH
        _delay_ms(500);
        PORTB &amp;= ~(1 &lt;&lt; PB5); // LOW
        _delay_ms(500);
    }
}
            </div>

            <div class="footer">
                <p>Hak Cipta &copy; 2026 SMK Perguruan Pembda Nikel — Diproduksi untuk LMS PembdaHUB</p>
            </div>
        </body>
        </html>
        ';

        $pdf = Pdf::loadHTML($htmlPdf)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        File::put($pdfPath, $pdfContent);
        File::put($publicPdfPath, $pdfContent);

        $fileSize = filesize($pdfPath);

        // 6. Material
        $materialContentHtml = '
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-3xl p-6 shadow-md border-2 border-black">
                <h2 class="text-xl font-black uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-microchip text-amber-400"></i> MODUL AJAR: PENGENALAN MIKROKONTROLER & OUTPUT DIGITAL
                </h2>
                <p class="text-amber-300 font-bold text-xs mt-1">Mata Pelajaran: Kosentrasi Keahlian TE | Kelas: X TAV | Pengampu: Yulianus Zega, S.Kom, M.Pd</p>
            </div>

            <div class="p-6 bg-white rounded-3xl border-2 border-black shadow-md space-y-4 text-black">
                <h3 class="text-base font-black text-black uppercase tracking-wider border-b-2 border-black pb-2"><i class="fas fa-book-reader text-emerald-600 mr-2"></i>Rangkuman Materi & Arsitektur Utama</h3>
                <p class="font-bold text-xs text-black leading-relaxed">
                    Mikrokontroler adalah komputer utuh dalam satu keping IC yang didalamnya terdapat CPU, RAM, Flash ROM, Timer/Counter, serta Register I/O.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="p-4 bg-amber-100 border-2 border-black rounded-2xl">
                        <span class="block font-black text-xs text-black uppercase"><i class="fas fa-memory mr-1"></i> Register DDRx</span>
                        <p class="text-[11px] font-bold text-black mt-1">Data Direction Register: <code>1</code> = Output, <code>0</code> = Input.</p>
                    </div>
                    <div class="p-4 bg-sky-100 border-2 border-black rounded-2xl">
                        <span class="block font-black text-xs text-black uppercase"><i class="fas fa-bolt mr-1"></i> Register PORTx</span>
                        <p class="text-[11px] font-bold text-black mt-1">Data Register: <code>1</code> = HIGH (5V), <code>0</code> = LOW (0V).</p>
                    </div>
                    <div class="p-4 bg-emerald-100 border-2 border-black rounded-2xl">
                        <span class="block font-black text-xs text-black uppercase"><i class="fas fa-eye mr-1"></i> Register PINx</span>
                        <p class="text-[11px] font-bold text-black mt-1">Input Pins Address. Membaca kondisi logika fisik pin.</p>
                    </div>
                </div>
            </div>
        </div>
        ';

        LmsMaterial::updateOrCreate(
            [
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => '[PDF] Modul Lengkap: Pemrograman Mikrokontroler & Output Digital',
            ],
            [
                'content' => $materialContentHtml,
                'material_type' => 'pdf',
                'file_path' => 'lms_materials/' . $pdfFileName,
                'file_size' => $fileSize,
                'order_number' => 1,
                'is_published' => true,
            ]
        );

        // 7. Tugas High-Level
        LmsAssignment::updateOrCreate(
            [
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => 'Tugas Proyek High-Level: Perancangan System Display Multiplexing & Dual-Relay Controller via Register Manipulation',
            ],
            [
                'description' => "SOAL TUGAS TINGKAT KESULITAN TINGGI (HOTS):\n\nPerancangan modul kendali tampilan 7-Segment Multiplexing 2-Digit dan Dual-Relay Driver untuk Beban AC 220V berbasis ATmega328P.\n\nKETENTUAN TEKNIS WAJIB:\n1. PERHITUNGAN ELEKTRONIKA (20 Poin): Hitung resistor pembatas arus LED & Resistor Basis Transistor NPN Relay 12V.\n2. SKEMATIK & PROTEKSI (20 Poin): Diagram skematik + Flyback Diode 1N4007.\n3. PROGRAMMING C EMBEDDED (40 Poin): Kode C register murni tanpa delay blocking, menggunakan Timer Interrupt.\n4. ANALISIS FMEA (20 Poin): Analisis moda kegagalan transistor short-circuit.",
                'assignment_type' => 'file_text',
                'deadline' => now()->addDays(14),
                'max_score' => 100,
                'allow_resubmit' => true,
                'is_published' => true,
            ]
        );

        // 8. Quiz + 10 Soal
        $quiz = LmsQuiz::updateOrCreate(
            [
                'course_id' => $course->id,
                'module_id' => $module->id,
                'title' => 'Quiz Evaluasi Modul 1: Mikrokontroler & Output Digital',
            ],
            [
                'description' => 'Quiz evaluasi 10 soal pilihan ganda HOTS menguji pemahaman arsitektur mikrokontroler, register I/O, bitwise operation, current sinking/sourcing, serta rangkaian driver beban.',
                'time_limit' => 20,
                'total_score' => 100,
                'passing_score' => 75,
                'max_attempts' => 2,
                'shuffle_questions' => true,
                'show_result' => true,
                'is_published' => true,
            ]
        );

        LmsQuizQuestion::where('quiz_id', $quiz->id)->delete();

        $questionsData = [
            [
                'question' => 'Pada mikrokontroler ATmega328P, register manakah yang berfungsi khusus untuk menentukan arah aliran data dari sebuah pin GPIO (apakah dikonfigurasi sebagai Input atau Output)?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'PORTB'],
                    ['key' => 'B', 'text' => 'DDRB'],
                    ['key' => 'C', 'text' => 'PINB'],
                    ['key' => 'D', 'text' => 'MCUCR'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 1,
            ],
            [
                'question' => 'Instruksi C bitwise manakah yang paling tepat untuk mengaktifkan (set HIGH) pin PB3 tanpa mengubah kondisi bit register PORTB lainnya?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'PORTB = (1 << PB3);'],
                    ['key' => 'B', 'text' => 'PORTB |= (1 << PB3);'],
                    ['key' => 'C', 'text' => 'PORTB &= ~(1 << PB3);'],
                    ['key' => 'D', 'text' => 'PORTB ^= ~(1 << PB3);'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 2,
            ],
            [
                'question' => 'Sebuah LED merah (Vf = 2.0 V, If = 15 mA) dihubungkan ke pin output Vcc = 5 V. Berapakah nilai resistor pembatas arus (Limiting Resistor) minimal yang tepat agar LED beroperasi aman?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => '100 Ohm'],
                    ['key' => 'B', 'text' => '200 Ohm (Standar 220 Ohm)'],
                    ['key' => 'C', 'text' => '330 Ohm'],
                    ['key' => 'D', 'text' => '1 kOhm'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 3,
            ],
            [
                'question' => 'Apa perbedaan utama antara mode konfigurasi Current Sinking dan Current Sourcing pada sambungan LED ke mikrokontroler?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Current Sinking: LED menyala saat pin berlogika LOW (0V). Current Sourcing: LED menyala saat pin berlogika HIGH (5V).'],
                    ['key' => 'B', 'text' => 'Current Sinking: LED menyala saat pin berlogika HIGH (5V). Current Sourcing: LED menyala saat pin berlogika LOW (0V).'],
                    ['key' => 'C', 'text' => 'Current Sinking tidak memerlukan resistor pembatas arus.'],
                    ['key' => 'D', 'text' => 'Current Sourcing hanya bisa digunakan pada pin analog.'],
                ],
                'correct_answer' => 'A',
                'score' => 10,
                'order_number' => 4,
            ],
            [
                'question' => 'Mengapa dioda pembalik (Flyback / Freewheeling Diode) wajib dipasang secara antiparalel pada kumparan relay yang dikendalikan oleh transistor switch?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Untuk mempercepat waktu saklar relay melompat.'],
                    ['key' => 'B', 'text' => 'Untuk menyerap induksi lonjakan tegangan kejut (Back-EMF) saat arus kumparan terputus tiba-tiba.'],
                    ['key' => 'C', 'text' => 'Untuk menurunkan tegangan Vcc 12V menjadi 5V.'],
                    ['key' => 'D', 'text' => 'Untuk menggantikan peran transistor NPN.'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 5,
            ],
            [
                'question' => 'Perintah C bitwise `PORTD ^= (1 << PD4);` yang dijalankan di dalam sebuah loop berfungsi untuk:',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Mematikan pin PD4 secara permanen.'],
                    ['key' => 'B', 'text' => 'Membangkitkan logika 5V terus menerus.'],
                    ['key' => 'C', 'text' => 'Membalikkan kondisi logika pin PD4 (Toggle: HIGH menjadi LOW, LOW menjadi HIGH).'],
                    ['key' => 'D', 'text' => 'Membaca status tombol pada pin PD4.'],
                ],
                'correct_answer' => 'C',
                'score' => 10,
                'order_number' => 6,
            ],
            [
                'question' => 'Teknik apakah yang digunakan untuk menampilkan angka pada display Seven-Segment 4-digit secara efisien hanya dengan menggunakan 8 pin data segmen dan 4 pin pemilih digit?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Pulse Width Modulation (PWM)'],
                    ['key' => 'B', 'text' => 'Display Multiplexing (Scanning cepat bergantian)'],
                    ['key' => 'C', 'text' => 'Direct Parallel Addressing'],
                    ['key' => 'D', 'text' => 'Analog Sinking Control'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 7,
            ],
            [
                'question' => 'Apabila pin PB2 dikonfigurasi sebagai Input dengan Pull-Up Internal aktif (`DDRB &= ~(1<<PB2); PORTB |= (1<<PB2);`), berapakah pembacaan logika pada `PINB & (1<<PB2)` ketika saklar ditekan ke Ground?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Logika HIGH (1 / 5V)'],
                    ['key' => 'B', 'text' => 'Logika LOW (0 / 0V)'],
                    ['key' => 'C', 'text' => 'Floating State'],
                    ['key' => 'D', 'text' => 'Tegangan High-Impedance'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 8,
            ],
            [
                'question' => 'Manakah jenis memori dalam mikrokontroler ATmega328P yang bersifat Non-Volatile dan khusus digunakan untuk menyimpan firmware / program .hex utama?',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'SRAM (Static Random Access Memory)'],
                    ['key' => 'B', 'text' => 'Flash Memory'],
                    ['key' => 'C', 'text' => 'Register File'],
                    ['key' => 'D', 'text' => 'DRAM'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 9,
            ],
            [
                'question' => 'Sebuah transistor NPN 2N2222 difungsikan sebagai saklar digital (Switch Mode) untuk menyalakan Relay 12V. Agar transistor berada pada daerah Jenuh (Saturation Region), syarat utama kondisi Vbe dan Vce adalah:',
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Vbe < 0.7 V dan Vce = 12 V'],
                    ['key' => 'B', 'text' => 'Vbe >= 0.7 V (Arus Basis Ib cukup) dan Vce mendekati 0 V (Vce sat ≈ 0.2 V)'],
                    ['key' => 'C', 'text' => 'Vbe = 0 V dan Vce = 5 V'],
                    ['key' => 'D', 'text' => 'Transistor harus bekerja di daerah Aktif Linear.'],
                ],
                'correct_answer' => 'B',
                'score' => 10,
                'order_number' => 10,
            ],
        ];

        foreach ($questionsData as $qData) {
            LmsQuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $qData['question'],
                'question_type' => $qData['question_type'],
                'options' => $qData['options'],
                'correct_answer' => $qData['correct_answer'],
                'score' => $qData['score'],
                'order_number' => $qData['order_number'],
            ]);
        }

        $quiz->update(['total_score' => $quiz->questions()->sum('score')]);
    }
}
