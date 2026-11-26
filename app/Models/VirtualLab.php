<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualLab extends Model
{
    protected $table = 'virtual_labs';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'subject_name',
        'engine_type',
        'description',
        'sop_steps',
        'custom_html',
        'course_id',
        'created_by',
        'icon',
        'accent_color',
        'is_active',
    ];

    protected $casts = [
        'sop_steps' => 'array',
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Ensure built-in multi-subject virtual labs exist (safe idempotent seeding).
     */
    public static function ensureDefaultLabs(): void
    {
        $defaults = [
            [
                'slug'         => 'mesin-bensin-4-tak',
                'title'        => 'Simulator Mesin Bensin 4-Tak',
                'category'     => 'vokasi',
                'subject_name' => 'Teknik Otomotif (TKR / TSM)',
                'engine_type'  => 'gasoline_engine',
                'icon'         => 'fas fa-cogs',
                'accent_color' => '#f97316',
                'description'  => 'Simulasi kerja silinder mesin bensin 4-langkah (Hisap, Kompresi, Usaha, Buang) dengan tuning RPM dan rasio campuran udara-bahan bakar.',
                'sop_steps'    => [
                    'Pastikan kondisi ruang lab aman dan periksa panel indikator suhu mesin sebelum menaikkan RPM.',
                    'Atur putaran mesin awal pada 1000 RPM (Idle) dan amati urutan 4 langkah piston serta pergerakan katup hisap & buang.',
                    'Lakukan tuning Rasio Udara-BBM (AFR) menuju 14.7:1 (stoikiometri) dan amati perubahan tenaga (HP) serta efisiensi pembakaran.',
                    'Naikkan RPM secara bertahap hingga 5000+ RPM dan catat kenaikan suhu serta daya output mesin pada tabel pengamatan.',
                ],
            ],
            [
                'slug'         => 'persamaan-reaksi-kimia',
                'title'        => 'Simulator Persamaan Reaksi Kimia',
                'category'     => 'sma',
                'subject_name' => 'Kimia (SMA)',
                'engine_type'  => 'chemical_reaction',
                'icon'         => 'fas fa-vial',
                'accent_color' => '#8b5cf6',
                'description'  => 'Visualisasi tumbukan molekul reaktan menjadi produk pada reaksi kimia lengkap dengan pengaturan suhu, konsentrasi, dan katalis.',
                'sop_steps'    => [
                    'Pilih jenis persamaan reaksi kimia yang akan diuji di tabung reaksi virtual.',
                    'Atur suhu awal (25°C) dan konsentrasi reaktan, lalu amati kecepatan tumbukan partikel berwarna.',
                    'Aktifkan Katalis untuk menurunkan energi aktivasi dan bandingkan waktu terbentuknya molekul produk.',
                    'Catat pengaruh kenaikan suhu terhadap laju reaksi dan kesetimbangan molekul.',
                ],
            ],
            [
                'slug'         => 'persamaan-kuadrat-parabola',
                'title'        => 'Simulator Persamaan Kuadrat & Parabola',
                'category'     => 'smp',
                'subject_name' => 'Matematika (SMP / SMA)',
                'engine_type'  => 'quadratic_graph',
                'icon'         => 'fas fa-square-root-alt',
                'accent_color' => '#10b981',
                'description'  => 'Eksplorasi grafik fungsi kuadrat y = ax² + bx + c secara interaktif dengan perhitungan otomatis Diskriminan, Titik Puncak, dan Akar-akar.',
                'sop_steps'    => [
                    'Atur koefisien a = 1, b = 0, c = -4 untuk melihat kurva parabola dasar yang memotong sumbu X di dua titik.',
                    'Ubah nilai koefisien (a) dari positif ke negatif dan amati perubahan arah bukaan parabola (ke atas vs ke bawah).',
                    'Geser konstanta (c) dan amati pergeseran titik potong sumbu Y serta nilai Diskriminan (D = b² - 4ac).',
                    'Cari kondisi di mana D = 0 (kurva menyinggung sumbu X tepat di satu titik).',
                ],
            ],
            [
                'slug'         => 'hukum-ohm-rangkaian-dc',
                'title'        => 'Lab Listrik & Hukum Ohm (V = I × R)',
                'category'     => 'umum',
                'subject_name' => 'IPA / Fisika / Teknik Elektronika',
                'engine_type'  => 'ohm_law',
                'icon'         => 'fas fa-bolt',
                'accent_color' => '#eab308',
                'description'  => 'Simulasi aliran elektron pada rangkaian DC dengan tuning tegangan sumber (Volt) dan hambatan (Ohm) untuk mengamati arus & daya lampu.',
                'sop_steps'    => [
                    'Mulai dengan tegangan aman 6 Volt dan hambatan 220 Ohm sebelum menyalakan rangkaian.',
                    'Amati kecepatan aliran elektron pada kabel virtual yang merepresentasikan besarnya kuat arus listrik (Ampere).',
                    'Naikkan tegangan menjadi 12V–24V dan catat perubahan daya (Watt) serta kecerahan lampu beban.',
                    'Turunkan hambatan di bawah 50 Ohm pada tegangan tinggi untuk mengamati peringatan Overcurrent (>500 mA).',
                ],
            ],
            [
                'slug'         => 'gerbang-logika-digital',
                'title'        => 'Lab Gerbang Logika Digital',
                'category'     => 'umum',
                'subject_name' => 'Informatika / TE / TKJ',
                'engine_type'  => 'logic_gate',
                'icon'         => 'fas fa-project-diagram',
                'accent_color' => '#6366f1',
                'description'  => 'Eksperimen gerbang logika AND, OR, NAND, NOR, XOR, dan NOT dengan saklar input biner (0/1), LED output, dan tabel kebenaran aktif.',
                'sop_steps'    => [
                    'Pilih jenis gerbang logika (mulai dari AND, lalu OR dan XOR).',
                    'Uji seluruh kombinasi Input A dan Input B (00, 01, 10, 11) dengan menekan tombol saklar.',
                    'Amati nyala LED Output (HIGH/1 vs LOW/0) dan cocokan dengan baris yang tersorot pada Tabel Kebenaran.',
                ],
            ],
            [
                'slug'         => 'beban-struktur-balok-dpib',
                'title'        => 'Simulator Beban & Lenturan Balok Struktur',
                'category'     => 'vokasi',
                'subject_name' => 'Desain Pemodelan & Info Bangunan (DPIB)',
                'engine_type'  => 'beam_load',
                'icon'         => 'fas fa-building',
                'accent_color' => '#06b6d4',
                'description'  => 'Uji kekuatan balok konstruksi (Beton, Baja, Kayu) terhadap beban terpusat dan panjang bentang untuk menganalisis lenturan struktur.',
                'sop_steps'    => [
                    'Pilih jenis material balok konstruksi (Kayu, Beton Bertulang, atau Baja Profil).',
                    'Atur panjang bentang balok (meter) dan beban terpusat (kN) sesuai skenario desain.',
                    'Amati animasi lenturan (defleksi) balok dan pastikan rasio keamanan tetap berada di zona Aman (Hijau).',
                ],
            ],
            [
                'slug'         => 'subnetting-ipv4-tkj',
                'title'        => 'Lab Subnetting IPv4 & CIDR Jaringan',
                'category'     => 'vokasi',
                'subject_name' => 'Teknik Komputer & Jaringan (TKJ/ACP)',
                'engine_type'  => 'subnetting',
                'icon'         => 'fas fa-network-wired',
                'accent_color' => '#0ea5e9',
                'description'  => 'Kalkulator dan visualisasi pembagian blok jaringan IPv4 (Network, Broadcast, Subnet Mask, dan Host Usable) secara real-time.',
                'sop_steps'    => [
                    'Masukkan alamat IPv4 target (misal 192.168.10.25).',
                    'Geser slider Prefix CIDR dari /24 ke /26, /27, atau /28 untuk membagi subnet lebih kecil.',
                    'Catat perubahan jumlah Host Usable, Network Address, dan Broadcast Address untuk konfigurasi router.',
                ],
            ],
            [
                'slug'         => 'fisika-gerak-gaya-newton',
                'title'        => 'Simulator Gerak & Gaya (Hukum Newton)',
                'category'     => 'sma',
                'subject_name' => 'Fisika / IPA (SMP & SMA)',
                'engine_type'  => 'physics_motion',
                'icon'         => 'fas fa-rocket',
                'accent_color' => '#0284c7',
                'description'  => 'Visualisasi vektor gaya dorong, gaya gesek, bidang miring, dan grafik kecepatan real-time sesuai Hukum I & II Newton.',
                'sop_steps'    => [
                    'Atur massa benda (kg) dan koefisien gesek lantai awal sebelum menjalankan simulasi.',
                    'Geser Gaya Dorong (N) secara bertahap dan amati kapan benda mulai bergerak mengatasi gaya gesek statis.',
                    'Amati perubahan panjang panah vektor biru (gaya dorong) dan merah (gaya gesek) serta grafik kecepatan vs waktu.',
                ],
            ],
            [
                'slug'         => 'biologi-pembelahan-sel-reproduksi',
                'title'        => 'Simulator Reproduksi & Pembelahan Sel (Mitosis/Meiosis)',
                'category'     => 'sma',
                'subject_name' => 'Biologi / IPA (SMP & SMA)',
                'engine_type'  => 'biology_cell',
                'icon'         => 'fas fa-dna',
                'accent_color' => '#ec4899',
                'description'  => 'Animasi siklus pembelahan sel Mitosis & Meiosis lengkap dengan pergerakan kromosom, sentriol, dan keterangan tiap fase.',
                'sop_steps'    => [
                    'Pilih mode Mitosis (sel tubuh 2n) terlebih dahulu dan amati 5 tahap: Profase, Metafase, Anafase, Telofase, Sitokinesis.',
                    'Gunakan slider kecepatan atau tombol fase untuk mengamati kromosom berjajar di ekuator pada Metafase.',
                    'Beralih ke mode Meiosis dan bandingkan jumlah serta sifat kromosom sel anak yang terbentuk.',
                ],
            ],
            [
                'slug'         => 'matematika-statistika-distribusi',
                'title'        => 'Simulator Statistika & Distribusi Data',
                'category'     => 'smp',
                'subject_name' => 'Matematika (SMP / SMA)',
                'engine_type'  => 'math_statistics',
                'icon'         => 'fas fa-chart-column',
                'accent_color' => '#10b981',
                'description'  => 'Eksplorasi histogram distribusi data dengan perhitungan otomatis Mean, Median, Modus, dan Simpangan Baku secara real-time.',
                'sop_steps'    => [
                    'Pilih preset Distribusi Normal dan amati posisi garis Mean serta Median pada histogram.',
                    'Tambahkan nilai ekstrem (outlier) dan amati bagaimana Mean bergeser lebih jauh dibanding Median.',
                    'Ubah jumlah kelas interval (Bins) untuk melihat pengaruh pengelompokan terhadap bentuk histogram.',
                ],
            ],
            [
                'slug'         => 'sosiologi-dinamika-sosial',
                'title'        => 'Simulator Dinamika Sosial & Penyebaran Norma',
                'category'     => 'umum',
                'subject_name' => 'Sosiologi / IPS',
                'engine_type'  => 'sociology_dynamics',
                'icon'         => 'fas fa-people-arrows',
                'accent_color' => '#f59e0b',
                'description'  => 'Model berbasis agen yang memvisualisasikan bagaimana interaksi sosial, tokoh masyarakat, dan keterbukaan memengaruhi penyebaran norma/inovasi.',
                'sop_steps'    => [
                    'Amati pergerakan partikel warga dan garis interaksi sosial yang terbentuk.',
                    'Ubah tingkat Keterbukaan Masyarakat dari rendah (10%) ke tinggi (80%) lalu bandingkan kecepatan adopsi norma.',
                    'Tambahkan jumlah Agen Perubahan (bintang kuning) dan catat perubahan persentase warga yang mengadopsi.',
                ],
            ],
            [
                'slug'         => 'bahasa-struktur-kalimat-tenses',
                'title'        => 'Simulator Struktur Kalimat (SPOK) & Tenses',
                'category'     => 'umum',
                'subject_name' => 'Bahasa Indonesia & Bahasa Inggris',
                'engine_type'  => 'language_syntax',
                'icon'         => 'fas fa-language',
                'accent_color' => '#6366f1',
                'description'  => 'Visualisasi blok warna fungsi kalimat (Subjek, Predikat, Objek, Keterangan) dan transformasi garis waktu Tenses secara interaktif.',
                'sop_steps'    => [
                    'Pilih kombinasi Subjek (tunggal/jamak), Predikat, dan Objek.',
                    'Ubah Kala Waktu (Past, Present, Continuous, Future) dan amati perubahan bentuk kata kerja serta penanda waktu.',
                    'Bandingkan pola blok warna Bahasa Indonesia dan Bahasa Inggris pada garis waktu.',
                ],
            ],
        ];

        foreach ($defaults as $item) {
            static::firstOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
