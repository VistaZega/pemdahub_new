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
        'room_key',
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
     * Pengelompokan standar berdasarkan Rumpun Ilmu / Jenis Laboratorium.
     */
    public function getDisciplineKeyAttribute(): string
    {
        if (!empty($this->room_key)) {
            return $this->room_key;
        }

        $eng = strtolower((string) $this->engine_type);
        $subj = strtolower((string) $this->subject_name . ' ' . (string) $this->title);

        if (in_array($eng, ['olympiad_5', 'ptn_speed', 'ptn_speed_advanced']) || str_contains($subj, 'olimpiade') || str_contains($subj, 'ptn') || str_contains($subj, 'snbt') || str_contains($subj, 'utbk')) {
            return 'olimpiade';
        }
        if (in_array($eng, ['gasoline_engine', 'beam_load', 'subnetting', 'logic_gate', 'vokasi_plc', 'vokasi_lan_topo', 'vokasi_cad_dpib']) || $this->category === 'vokasi' || str_contains($subj, 'smk') || str_contains($subj, 'tkj') || str_contains($subj, 'tkr') || str_contains($subj, 'dpib')) {
            return 'vokasi';
        }
        if (in_array($eng, ['quadratic_graph', 'math_statistics', 'math_trigonometry', 'math_probability', 'math_transform', 'math_integral', 'math_trig_circle']) || str_contains($subj, 'matematika') || str_contains($subj, 'gradien') || str_contains($subj, 'aljabar') || str_contains($subj, 'geometri') || str_contains($subj, 'kalkulus')) {
            return 'matematika';
        }
        if (in_array($eng, ['chemical_reaction', 'chem_ph', 'chem_atom', 'chem_stoichiometry', 'biology_cell', 'bio_photosynthesis', 'bio_blood', 'bio_genetics', 'bio_osmosis', 'bio_nervous']) || str_contains($subj, 'kimia') || str_contains($subj, 'biologi')) {
            return 'kimia_biologi';
        }
        if (in_array($eng, ['sociology_dynamics', 'language_syntax', 'econ_market', 'econ_compound', 'econ_inflation']) || str_contains($subj, 'ekonomi') || str_contains($subj, 'sosiologi') || str_contains($subj, 'bahasa')) {
            return 'ips_bahasa';
        }
        if (in_array($eng, ['physics_motion', 'physics_parabola', 'physics_wave', 'physics_archimedes', 'physics_doppler', 'physics_pulley', 'physics_optics', 'physics_elevator', 'physics_electric_field', 'ipa_greenhouse', 'ipa_renewable', 'ohm_law']) || str_contains($subj, 'fisika')) {
            return 'fisika';
        }

        return 'fisika';
    }

    public function getDisciplineLabelAttribute(): string
    {
        return match ($this->discipline_key) {
            'olimpiade'     => '🏆 Lab Olimpiade & PTN',
            'fisika'        => 'Lab IPA & Fisika',
            'kimia_biologi' => 'Lab Kimia & Biologi',
            'matematika'    => 'Lab Matematika',
            'vokasi'        => 'Lab Kejuruan & Vokasi',
            'ips_bahasa'    => 'Lab IPS, Ekonomi & Bahasa',
            default         => 'Lab Riset Umum',
        };
    }

    /**
     * Paket Teori, Hukum Alam, Rumus Kunci, dan Soal HOTS untuk belajar mandiri siswa.
     */
    public function getTheoryAndChallengeAttribute(): array
    {
        $map = [
            'physics_motion' => [
                'law'         => 'Hukum I & II Newton tentang Gerak',
                'theory'      => 'Benda diam akan tetap diam kecuali ada resultan gaya tak nol yang bekerja padanya. Jika gaya dorong lebih besar dari gaya gesek maksimum, benda mengalami percepatan searah gaya.',
                'formula'     => 'ΣF = m × a   |   f_gesek = μ × N',
                'variables'   => 'ΣF = resultan gaya (N), m = massa (kg), a = percepatan (m/s²), μ = koefisien gesek, N = gaya normal (N)',
                'challenge_q' => 'Mobil bermassa 20 kg didorong gaya 100 N di lantai datar dengan gaya gesek 20 N. Berapakah percepatan mobil?',
                'options'     => ['2 m/s²', '4 m/s²', '5 m/s²', '6 m/s²'],
                'answer_idx'  => 1,
                'solution'    => 'ΣF = F_dorong - f_gesek = 100 - 20 = 80 N. Maka a = ΣF / m = 80 / 20 = 4 m/s².',
            ],
            'physics_parabola' => [
                'law'         => 'Superposisi Gerak Lurus Beraturan (GLB) & Gerak Vertikal (GLBB)',
                'theory'      => 'Gerak parabola adalah perpaduan gerak horizontal dengan kecepatan tetap (v_x = v₀ cos α) dan gerak vertikal yang dipengaruhi gravitasi (v_y = v₀ sin α - g·t).',
                'formula'     => 'H_max = (v₀² · sin²α) / (2g)   |   X_max = (v₀² · sin 2α) / g',
                'variables'   => 'v₀ = kecepatan awal (m/s), α = sudut elevasi (°), g = percepatan gravitasi (10 m/s²)',
                'challenge_q' => 'Peluru ditembakkan dengan v₀ = 40 m/s pada sudut 30° (sin 60° = 0,866, g = 10 m/s²). Berapa jarak terjauh (X_max)?',
                'options'     => ['80,0 m', '138,6 m', '160,0 m', '69,3 m'],
                'answer_idx'  => 1,
                'solution'    => 'X_max = (40² × sin(2×30°)) / 10 = (1600 × 0,866) / 10 = 138,56 ≈ 138,6 m.',
            ],
            'physics_archimedes' => [
                'law'         => 'Hukum Archimedes (Gaya Apung Fluida)',
                'theory'      => 'Setiap benda yang dicelupkan sebagian atau seluruhnya ke dalam fluida mengalami gaya ke atas sebesar berat fluida yang dipindahkan oleh benda tersebut.',
                'formula'     => 'F_a = ρ_cair × g × V_celup',
                'variables'   => 'F_a = gaya apung (N), ρ_cair = massa jenis cairan (kg/m³), V_celup = volume tercelup (m³)',
                'challenge_q' => 'Sebuah balok bervolume 0,02 m³ tercelup seluruhnya dalam air (ρ = 1000 kg/m³, g = 10 m/s²). Berapakah gaya apungnya?',
                'options'     => ['20 N', '100 N', '200 N', '2000 N'],
                'answer_idx'  => 2,
                'solution'    => 'F_a = ρ × g × V = 1000 × 10 × 0,02 = 200 N.',
            ],
            'physics_doppler' => [
                'law'         => 'Efek Doppler pada Gelombang Bunyi',
                'theory'      => 'Frekuensi bunyi yang didengar pengamat berubah ketika sumber bunyi atau pengamat bergerak relatif satu sama lain. Mendekat membuat muka gelombang merapat (frekuensi naik).',
                'formula'     => 'f_p = ((v ± v_p) / (v ∓ v_s)) × f_s',
                'variables'   => 'v = cepat rambat bunyi (340 m/s), v_p = kecepatan pengamat, v_s = kecepatan sumber, f_s = frekuensi sumber (Hz)',
                'challenge_q' => 'Sirine ambulans (f_s = 640 Hz) bergerak mendekati pengamat diam (v_p = 0) dengan kecepatan 20 m/s (v = 340 m/s). Berapa frekuensi terdengar?',
                'options'     => ['600 Hz', '640 Hz', '680 Hz', '720 Hz'],
                'answer_idx'  => 2,
                'solution'    => 'Sumber mendekat → tanda penyebut minus: f_p = (340 / (340 - 20)) × 640 = (340 / 320) × 640 = 680 Hz.',
            ],
            'physics_pulley' => [
                'law'         => 'Keuntungan Mekanis Katrol & Hukum Kekekalan Usaha',
                'theory'      => 'Katrol majemuk membagi beban ke sejumlah tali penopang (n), sehingga gaya kuasa yang dibutuhkan jauh lebih kecil, namun tali harus ditarik n kali lebih panjang.',
                'formula'     => 'F_kuasa = W / KM   |   KM = jumlah tali penopang (n)',
                'variables'   => 'W = berat beban (N), F_kuasa = gaya tarik (N), KM = keuntungan mekanis',
                'challenge_q' => 'Beban bermassa 60 kg (W = 600 N) diangkat menggunakan sistem takal dengan 4 tali penopang (KM = 4). Berapa gaya kuasa minimalnya?',
                'options'     => ['100 N', '150 N', '240 N', '300 N'],
                'answer_idx'  => 1,
                'solution'    => 'F_kuasa = W / KM = 600 / 4 = 150 N.',
            ],
            'physics_wave' => [
                'law'         => 'Persamaan Dasar Gelombang Mekanik',
                'theory'      => 'Gelombang merambatkan energi tanpa memindahkan materi secara permanen. Cepat rambat bergantung pada perkalian panjang gelombang dan frekuensinya.',
                'formula'     => 'v = λ × f   |   T = 1 / f',
                'variables'   => 'v = cepat rambat (m/s), λ = panjang gelombang (m), f = frekuensi (Hz), T = periode (s)',
                'challenge_q' => 'Dalam 4 detik terbentuk 8 gelombang penuh dengan panjang 1 gelombang = 3 meter. Berapakah cepat rambat gelombangnya?',
                'options'     => ['1,5 m/s', '3 m/s', '6 m/s', '12 m/s'],
                'answer_idx'  => 2,
                'solution'    => 'f = 8 / 4 = 2 Hz. Maka v = λ × f = 3 × 2 = 6 m/s.',
            ],
            'ohm_law' => [
                'law'         => 'Hukum Ohm & Daya Listrik Rangkaian DC',
                'theory'      => 'Kuat arus listrik berbanding lurus dengan tegangan dan berbanding terbalik dengan hambatan total rangkaian. Pada rangkaian seri R_tot = R₁ + R₂, pada paralel 1/R_tot = 1/R₁ + 1/R₂.',
                'formula'     => 'V = I × R   |   P = V × I = I² × R',
                'variables'   => 'V = tegangan (Volt), I = arus (Ampere), R = hambatan (Ohm), P = daya (Watt)',
                'challenge_q' => 'Dua resistor 6 Ω dan 3 Ω dipasang PARALEL pada baterai 12 V. Berapakah kuat arus total yang keluar dari baterai?',
                'options'     => ['2 A', '4 A', '6 A', '9 A'],
                'answer_idx'  => 2,
                'solution'    => '1/R_p = 1/6 + 1/3 = 3/6 → R_p = 2 Ω. Maka I = V / R_p = 12 / 2 = 6 A.',
            ],
            'chem_ph' => [
                'law'         => 'Derajat Keasaman (Skala pH Sorensen)',
                'theory'      => 'Keasaman larutan ditentukan oleh konsentrasi ion H⁺. Semakin besar [H⁺], semakin kecil nilai pH (di bawah 7 = Asam; tepat 7 = Netral; di atas 7 = Basa).',
                'formula'     => 'pH = -log[H⁺]   |   pH + pOH = 14',
                'variables'   => '[H⁺] = molaritas ion hidrogen (M), pOH = -log[OH⁻]',
                'challenge_q' => 'Berapakah pH larutan basa kuat NaOH dengan konsentrasi 0,001 M (10⁻³ M)?',
                'options'     => ['3', '7', '11', '13'],
                'answer_idx'  => 2,
                'solution'    => 'pOH = -log(10⁻³) = 3. Maka pH = 14 - 3 = 11 (Basa Kuat).',
            ],
            'chem_stoichiometry' => [
                'law'         => 'Hukum Perbandingan Tetap & Pereaksi Pembatas',
                'theory'      => 'Dalam reaksi kimia, zat yang habis bereaksi lebih dulu disebut Pereaksi Pembatas, dan dialah yang menentukan jumlah maksimum produk yang terbentuk.',
                'formula'     => 'mol = massa / Mr   |   mol_produk = (koef_produk / koef_pembatas) × mol_pembatas',
                'variables'   => 'Mr = massa molekul relatif (g/mol)',
                'challenge_q' => 'Pada reaksi 2H₂ + O₂ → 2H₂O, direaksikan 6 mol H₂ dan 2 mol O₂. Berapakah mol H₂O yang terbentuk dan zat apa yang bersisa?',
                'options'     => ['4 mol H₂O, sisa 2 mol H₂', '6 mol H₂O, sisa 1 mol O₂', '2 mol H₂O, sisa 4 mol H₂', '4 mol H₂O, habis semua'],
                'answer_idx'  => 0,
                'solution'    => 'Bagi mol dengan koefisien: H₂ = 6/2 = 3, O₂ = 2/1 = 2 (terkecil → O₂ pembatas). Maka H₂O = 2 × 2 = 4 mol, H₂ terpakai = 4 mol → sisa 2 mol H₂.',
            ],
            'math_probability' => [
                'law'         => 'Peluang Klasik & Frekuensi Harapan',
                'theory'      => 'Peluang suatu kejadian adalah rasio banyaknya hasil yang diinginkan n(A) terhadap seluruh ruang sampel n(S) yang mungkin terjadi.',
                'formula'     => 'P(A) = n(A) / n(S)   |   F_h = P(A) × N',
                'variables'   => 'n(A) = titik sampel kejadian, n(S) = total ruang sampel, N = banyak percobaan',
                'challenge_q' => 'Dua dadu dilempar bersamaan sekali. Berapakah peluang munculnya mata dadu berjumlah 7?',
                'options'     => ['1/12', '1/6', '5/36', '7/36'],
                'answer_idx'  => 1,
                'solution'    => 'n(S) = 6 × 6 = 36. Jumlah 7: (1,6),(2,5),(3,4),(4,3),(5,2),(6,1) → n(A) = 6. Peluang = 6/36 = 1/6.',
            ],
            'physics_optics' => [
                'law'         => 'Hukum Pembiasan Lensa & Pembentukan Bayangan',
                'theory'      => 'Lensa cembung (konvergen) mengumpulkan sinar cahaya dan membentuk bayangan nyata (terbalik) jika benda di luar fokus, atau bayangan maya (tegak, diperbesar) jika benda di dalam fokus. Lensa cekung selalu membentuk bayangan maya dan diperkecil.',
                'formula'     => '1/f = 1/s + 1/s\'   |   M = -s\'/s = h\'/h',
                'variables'   => 'f = jarak fokus (cm), s = jarak benda (cm), s\' = jarak bayangan (cm), M = perbesaran linier',
                'challenge_q' => 'Benda diletakkan 20 cm di depan lensa cembung berfokal 15 cm. Berapa jarak bayangan dan sifatnya?',
                'options'     => ['s\'=30 cm, maya', 's\'=60 cm, nyata terbalik', 's\'=12 cm, nyata', 's\'=-60 cm, maya tegak'],
                'answer_idx'  => 1,
                'solution'    => '1/s\' = 1/f - 1/s = 1/15 - 1/20 = (4-3)/60 = 1/60 → s\'=60 cm. Positif → nyata, terbalik. M = -60/20 = -3× (diperbesar 3 kali).',
            ],
            'physics_elevator' => [
                'law'         => 'Hukum Newton II pada Gerak Vertikal (Berat Semu di Lift)',
                'theory'      => 'Berat semu adalah gaya normal (N) yang dirasakan orang di atas timbangan dalam lift. Saat lift naik dengan percepatan, N > W (lebih berat). Saat lift turun dengan percepatan, N < W (lebih ringan). Saat lift diam atau GLB, N = W.',
                'formula'     => 'ΣF = m × a   |   W_semu = m(g + a) naik   |   W_semu = m(g - a) turun',
                'variables'   => 'm = massa (kg), g = 10 m/s², a = percepatan lift (m/s²), W = berat sejati (N), N = gaya normal/berat semu (N)',
                'challenge_q' => 'Orang bermassa 70 kg berada di lift yang bergerak turun dengan perlambatan 4 m/s². Berapakah berat semunya?',
                'options'     => ['420 N', '700 N', '980 N', '1050 N'],
                'answer_idx'  => 2,
                'solution'    => 'Turun dengan perlambatan = percepatan ke ATAS. W_semu = m(g+a) = 70×(10+4) = 70×14 = 980 N (lebih berat dari berat sejati 700 N).',
            ],
            'physics_electric_field' => [
                'law'         => 'Hukum Coulomb & Medan Listrik',
                'theory'      => 'Dua muatan listrik mengalami gaya tarik-menarik (berbeda tanda) atau tolak-menolak (sama tanda) yang sebanding dengan perkalian kedua muatan dan berbanding terbalik dengan kuadrat jaraknya.',
                'formula'     => 'F = k|Q1 × Q2| / r²   |   E = kQ / r²   |   k = 9 × 10⁹ N·m²/C²',
                'variables'   => 'F = gaya Coulomb (N), Q = muatan (C), r = jarak (m), E = kuat medan listrik (N/C)',
                'challenge_q' => 'Dua muatan +3 µC dan -3 µC berjarak 3 cm. Berapa gaya tarik-menariknya? (k=9×10⁹)',
                'options'     => ['30 N', '90 N', '270 N', '9 N'],
                'answer_idx'  => 1,
                'solution'    => 'F = k|Q1Q2|/r² = 9×10⁹ × (3×10⁻⁶)² / (0.03)² = 9×10⁹ × 9×10⁻¹² / 9×10⁻⁴ = 90 N.',
            ],
            'bio_osmosis' => [
                'law'         => 'Hukum Osmosis & Tekanan Osmotik Sel',
                'theory'      => 'Osmosis adalah perpindahan molekul air dari larutan hipotonik (konsentrasi zat terlarut rendah) ke larutan hipertonik melalui membran semipermeabel. Sel pada larutan hipotonik → turgid; larutan hipertonik → plasmolisis/krenasi.',
                'formula'     => 'Pi = iMRT   |   Hipotonik → air masuk   |   Hipertonik → air keluar',
                'variables'   => 'Pi = tekanan osmotik, i = faktor van\'t Hoff, M = molaritas, R = tetapan gas, T = suhu (K)',
                'challenge_q' => 'Sel darah merah dimasukkan ke larutan NaCl 20%. Apa yang terjadi?',
                'options'     => ['Sel mengembang (turgid)', 'Sel mengkerut (krenasi)', 'Sel tidak berubah (isotonik)', 'Sel membelah diri'],
                'answer_idx'  => 1,
                'solution'    => 'NaCl 20% jauh lebih pekat dari cairan sel darah merah (~0.9%). Larutan hipertonik menyebabkan air keluar dari sel secara osmosis → sel mengkerut (krenasi).',
            ],
            'bio_nervous' => [
                'law'         => 'Penjalaran Impuls Saraf & Hukum "All or Nothing"',
                'theory'      => 'Impuls saraf adalah perubahan potensial listrik pada membran neuron (depolarisasi → repolarisasi). Jalur refleks tidak melalui otak melainkan langsung melalui sumsum tulang belakang sehingga responnya sangat cepat.',
                'formula'     => 'Jalur biasa: Reseptor → Sensorik → Otak → Motorik → Efektor\nJalur refleks: Reseptor → Sensorik → Sumsum Tulang Belakang → Motorik → Efektor',
                'variables'   => 'Neuron sensorik = aferen (menuju otak/SSP), Neuron motorik = eferen (dari otak/SSP), Sinapsis = celah antar neuron',
                'challenge_q' => 'Saat tangan menyentuh benda panas, gerak refleks terjadi TANPA melalui otak. Bagian mana yang berperan sebagai pusat refleks?',
                'options'     => ['Otak besar (Cerebrum)', 'Otak kecil (Cerebellum)', 'Sumsum Tulang Belakang', 'Saraf Otonom'],
                'answer_idx'  => 2,
                'solution'    => 'Gerak refleks dikoordinasikan oleh sumsum tulang belakang (medula spinalis). Itulah mengapa respons refleks sangat cepat — impuls tidak perlu menempuh perjalanan ke otak.',
            ],
            'math_integral' => [
                'law'         => 'Teorema Dasar Kalkulus (Integral Pasti)',
                'theory'      => 'Integral pasti ∫[a→b] f(x)dx merepresentasikan luas daerah di bawah kurva f(x) dari x=a hingga x=b. Aproksimasi Riemann menggunakan n persegi panjang tipis; semakin besar n, semakin mendekati nilai eksak.',
                'formula'     => '∫[a→b] f(x)dx = F(b) - F(a)   |   Riemann: Σ f(xᵢ)·Δx, Δx=(b-a)/n',
                'variables'   => 'F(x) = anti-turunan f(x), a = batas bawah, b = batas atas, n = jumlah partisi',
                'challenge_q' => 'Hitung ∫[0→3] x² dx menggunakan rumus anti-turunan.',
                'options'     => ['6', '9', '12', '27/4'],
                'answer_idx'  => 1,
                'solution'    => 'Anti-turunan x² adalah x³/3. Maka ∫[0→3] x² dx = [x³/3] dari 0 ke 3 = (27/3) - (0/3) = 9 - 0 = 9.',
            ],
            'math_trig_circle' => [
                'law'         => 'Fungsi Trigonometri pada Lingkaran Satuan',
                'theory'      => 'Pada lingkaran satuan (r=1), koordinat titik P(θ) adalah (cos θ, sin θ). Fungsi sin memberikan ordinat (y), cos memberikan absis (x), dan tan = sin/cos = y/x dari titik pada lingkaran satuan.',
                'formula'     => 'sin²θ + cos²θ = 1   |   tan θ = sin θ / cos θ   |   sin(A+B) = sinA cosB + cosA sinB',
                'variables'   => 'θ = sudut (°atau radian), sin θ ∈ [-1,1], cos θ ∈ [-1,1], tan θ ∈ (-∞,+∞)',
                'challenge_q' => 'Nilai sin 30° × cos 60° + cos 30° × sin 60° adalah...',
                'options'     => ['1/2', '1', 'akar 3 / 2', 'akar 2 / 2'],
                'answer_idx'  => 1,
                'solution'    => 'Ini adalah rumus sin(A+B). sin 30° cos 60° + cos 30° sin 60° = sin(30°+60°) = sin 90° = 1.',
            ],
            'vokasi_lan_topo' => [
                'law'         => 'Topologi Jaringan LAN & Kebutuhan Kabel',
                'theory'      => 'Topologi jaringan menentukan cara perangkat terhubung. Bus: satu kabel backbone; Star: semua ke switch pusat; Ring: cincin tertutup; Mesh: setiap node terhubung ke semua node lain.',
                'formula'     => 'Mesh penuh: jumlah link = n(n-1)/2   |   Star: n link untuk n host',
                'variables'   => 'n = jumlah host/node, link = jumlah kabel/koneksi yang dibutuhkan',
                'challenge_q' => 'Jaringan mesh penuh dengan 5 host membutuhkan berapa banyak link/kabel?',
                'options'     => ['5 link', '8 link', '10 link', '25 link'],
                'answer_idx'  => 2,
                'solution'    => 'Mesh penuh: n(n-1)/2 = 5×4/2 = 10 link. Mesh menjamin konektivitas penuh tapi paling mahal.',
            ],
            'vokasi_cad_dpib' => [
                'law'         => 'Skala Gambar Teknik & Prinsip CAD',
                'theory'      => 'Gambar teknik bangunan menggunakan skala tertentu (misal 1:100) di mana 1 cm di kertas = 100 cm di lapangan. Denah adalah tampak atas bangunan yang menunjukkan tata letak ruangan, dinding, pintu, dan jendela.',
                'formula'     => 'Skala = Jarak gambar / Jarak nyata   |   Luas = p × l   |   Keliling = 2(p+l)',
                'variables'   => 'Skala 1:100 → 1 cm gambar = 1 m nyata; Skala 1:200 → 1 cm gambar = 2 m nyata',
                'challenge_q' => 'Ruang tamu 4m × 5m pada skala 1:100 memiliki ukuran pada gambar sebesar...',
                'options'     => ['40 cm × 50 cm', '4 cm × 5 cm', '0.4 cm × 0.5 cm', '400 cm × 500 cm'],
                'answer_idx'  => 1,
                'solution'    => 'Skala 1:100 → setiap 1 cm gambar = 100 cm nyata. Ruang 400cm × 500cm nyata → pada gambar = 400/100 × 500/100 = 4 cm × 5 cm.',
            ],
            'econ_inflation' => [
                'law'         => 'Hukum Inflasi & Teori Daya Beli Riil',
                'theory'      => 'Inflasi adalah kenaikan harga umum secara terus-menerus. Inflasi mengikis daya beli: uang yang sama membeli lebih sedikit barang. Daya beli riil = nilai nominal / (1+r)^n, di mana r = tingkat inflasi dan n = jumlah tahun.',
                'formula'     => 'IHK = (Harga sekarang / Harga dasar) × 100   |   Daya beli riil = Nominal / (1+r)^n',
                'variables'   => 'IHK = Indeks Harga Konsumen, r = tingkat inflasi (desimal), n = jumlah periode (tahun)',
                'challenge_q' => 'Jika inflasi 10% per tahun, daya beli uang Rp 10 juta setelah 5 tahun setara...',
                'options'     => ['Rp 6.000.000', 'Rp 6.209.213', 'Rp 5.000.000', 'Rp 7.500.000'],
                'answer_idx'  => 1,
                'solution'    => 'Daya beli riil = 10.000.000 / (1,10)^5 = 10.000.000 / 1,61051 ≈ Rp 6.209.213. Inflasi 10%/tahun menghilangkan sekitar 38% daya beli dalam 5 tahun.',
            ],
            'ptn_speed_advanced' => [
                'law'         => 'Strategi Penyelesaian Soal SNBT/UTBK Lintas Mapel',
                'theory'      => 'SNBT/UTBK menguji kemampuan Penalaran Umum, Pengetahuan & Pemahaman Umum, Pengetahuan Kuantitatif, Literasi Bahasa Indonesia, dan Literasi Bahasa Inggris. Setiap soal memiliki bobot sama, jadi manajemen waktu sangat krusial.',
                'formula'     => 'Skor UTBK = benar × 1 + salah × 0 (tidak ada pengurangan). Target: >700/1000 untuk PTN Favorit.',
                'variables'   => 'Waktu per soal: rata-rata 90 detik (PU/PPU) hingga 60 detik (PK/Literasi). Strategi: skip soal sulit, kerjakan yang mudah dulu.',
                'challenge_q' => 'Barisan geometri: 2, 6, 18, 54, ... Suku ke-6 adalah...',
                'options'     => ['108', '162', '486', '243'],
                'answer_idx'  => 2,
                'solution'    => 'Rasio r = 6/2 = 3 (geometri). Suku ke-5 = 54×3=162, suku ke-6 = 162×3=486. Rumus: Un = 2×3^(n-1), U6 = 2×3^5 = 2×243 = 486.',
            ],
        ];

        if (isset($map[$this->engine_type])) {
            return $map[$this->engine_type];
        }

        return [
            'law'         => 'Prinsip & Pemodelan Ilmiah ' . $this->subject_name,
            'theory'      => $this->description,
            'formula'     => 'Eksperimen Variabel Bebas ➔ Efek Variabel Terikat',
            'variables'   => 'Gunakan kontrol/slider pada ruang praktikum untuk menguji hubungan sebab-akibat antar variabel.',
            'challenge_q' => 'Manakah langkah ilmiah paling tepat ketika menguji pengaruh satu variabel terhadap fenomena di lab ini?',
            'options'     => [
                'Mengubah semua variabel sekaligus secara acak',
                'Mengubah satu variabel bebas sambil menjaga variabel lain tetap konstan',
                'Mengabaikan SOP lab dan langsung menebak hasil',
                'Tidak mencatat hasil pengamatan',
            ],
            'answer_idx'  => 1,
            'solution'    => 'Dalam metode ilmiah (variabel kontrol), hanya satu variabel bebas yang diubah pada satu waktu agar pengaruhnya terhadap hasil dapat diukur secara akurat.',
        ];
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
            [
                'slug'         => 'kimia-asam-basa-ph',
                'title'        => 'Simulator Kimia: Skala pH, Larutan Asam-Basa & Lakmus',
                'category'     => 'sma',
                'subject_name' => 'Kimia / IPA (SMP & SMA)',
                'engine_type'  => 'chem_ph',
                'icon'         => 'fas fa-flask',
                'accent_color' => '#a855f7',
                'description'  => 'Gelas reaksi pH dengan sampel larutan nyata (Asam Lambung, Cuka, Air, Sabun, NaOH), perubahan warna kertas lakmus, ion H⁺/OH⁻, dan pembuktian soal pH.',
                'sop_steps'    => [
                    'Pilih sampel larutan nyata (Cuka, Air Murni, Sabun) dan amati warna larutan serta kertas lakmus.',
                    'Amati perbandingan jumlah partikel ion H⁺ (asam) dan OH⁻ (basa) di dalam gelas kimia.',
                    'Klik tombol Uji Kasus Soal untuk membuktikan perhitungan pH = -log[H⁺] secara visual.',
                ],
            ],
            [
                'slug'         => 'biologi-fotosintesis-ingenhousz',
                'title'        => 'Simulator Biologi: Percobaan Fotosintesis Ingenhousz',
                'category'     => 'smp',
                'subject_name' => 'Biologi / IPA (SMP & SMA)',
                'engine_type'  => 'bio_photosynthesis',
                'icon'         => 'fas fa-leaf',
                'accent_color' => '#22c55e',
                'description'  => 'Visualisasi percobaan nyata Hydrilla dalam corong kaca & tabung reaksi menghasilkan gelembung gas O₂ saat cahaya, kadar CO₂, dan suhu diatur.',
                'sop_steps'    => [
                    'Nyalakan lampu pada intensitas 70% dan amati gelembung Oksigen (O₂) naik ke puncak tabung reaksi.',
                    'Uji penambahan NaHCO₃ (CO₂) dan catat peningkatan laju gelembung per menit.',
                    'Naikkan suhu air di atas 45°C dan amati berhentinya fotosintesis akibat denaturasi enzim.',
                ],
            ],
            [
                'slug'         => 'fisika-gerak-parabola',
                'title'        => 'Simulator Fisika: Gerak Parabola (Meriam & Bola)',
                'category'     => 'sma',
                'subject_name' => 'Fisika (SMA)',
                'engine_type'  => 'physics_parabola',
                'icon'         => 'fas fa-bullseye',
                'accent_color' => '#0ea5e9',
                'description'  => 'Tembakan proyektil nyata dengan lintasan melengkung, perhitungan Tinggi Maksimum (Hmax), Jarak Terjauh (Xmax), dan pembuktian soal gerak parabola.',
                'sop_steps'    => [
                    'Atur kecepatan awal v₀ = 30 m/s dan sudut elevasi 45°, lalu tembakkan bola/peluru.',
                    'Bandingkan jarak tembak pada sudut 30°, 45°, dan 60° untuk membuktikan sudut 45° terjauh.',
                    'Gunakan tombol Uji Soal untuk mencocokkan perhitungan rumus dengan lintasan nyata.',
                ],
            ],
            [
                'slug'         => 'matematika-trigonometri-menara',
                'title'        => 'Simulator Matematika: Trigonometri Mengukur Tinggi Menara',
                'category'     => 'sma',
                'subject_name' => 'Matematika (SMA / SMK)',
                'engine_type'  => 'math_trigonometry',
                'icon'         => 'fas fa-ruler-combined',
                'accent_color' => '#10b981',
                'description'  => 'Penerapan nyata Sin, Cos, Tan untuk menghitung tinggi menara menggunakan jarak pengamat dan sudut elevasi klinometer, lengkap dengan contoh soal.',
                'sop_steps'    => [
                    'Atur jarak pengamat ke kaki menara (sisi Samping) dan sudut pandang klinometer (θ).',
                    'Amati perubahan tinggi menara (sisi Depan) dan garis pandang miring secara real-time.',
                    'Klik Uji Soal untuk melihat pembuktian rumus tan(θ) = Depan / Samping.',
                ],
            ],
            [
                'slug'         => 'ekonomi-keseimbangan-pasar',
                'title'        => 'Simulator Ekonomi: Permintaan, Penawaran & Harga Pasar',
                'category'     => 'sma',
                'subject_name' => 'Ekonomi / IPS (SMP & SMA)',
                'engine_type'  => 'econ_market',
                'icon'         => 'fas fa-scale-balanced',
                'accent_color' => '#f59e0b',
                'description'  => 'Visualisasi kurva Permintaan (Demand) dan Penawaran (Supply) yang menentukan Harga Keseimbangan (Pe) dan Jumlah Keseimbangan (Qe) di pasar.',
                'sop_steps'    => [
                    'Amati titik potong awal kurva Demand (biru) dan Supply (merah) sebagai Harga Keseimbangan.',
                    'Geser permintaan ke kanan (lonjakan pembeli) dan catat kenaikan harga keseimbangan baru.',
                    'Uji kasus nyata kelangkaan pasokan (Supply turun) dan amati dampaknya terhadap harga.',
                ],
            ],
            [
                'slug'         => 'ipa-gelombang-bunyi',
                'title'        => 'Simulator IPA / Fisika: Gelombang, Frekuensi & Amplitudo',
                'category'     => 'smp',
                'subject_name' => 'IPA / Fisika (SMP & SMA)',
                'engine_type'  => 'physics_wave',
                'icon'         => 'fas fa-wave-square',
                'accent_color' => '#06b6d4',
                'description'  => 'Animasi gelombang transversal berpartikel dengan pengaturan Frekuensi (f), Panjang Gelombang (λ), Amplitudo (A), dan pembuktian soal cepat rambat v = λ × f.',
                'sop_steps'    => [
                    'Ubah nilai Frekuensi (Hz) dan amati perubahan kecepatan getaran partikel kuning.',
                    'Geser Panjang Gelombang (λ) dan perhatikan jarak antar puncak gelombang.',
                    'Klik Uji Soal untuk membuktikan perhitungan cepat rambat v = λ × f.',
                ],
            ],
            [
                'slug'         => 'bio-peredaran-darah',
                'title'        => 'Simulator Biologi: Pompa Jantung & Peredaran Darah Ganda',
                'category'     => 'smp',
                'subject_name' => 'Biologi / IPA (SMP & SMA)',
                'engine_type'  => 'bio_blood',
                'icon'         => 'fas fa-heart-pulse',
                'accent_color' => '#e11d48',
                'description'  => 'Bagaimana jantung memompa darah kotor ke paru-paru dan darah kaya oksigen ke seluruh tubuh tanpa pernah tercampur? Mengapa bilik kiri jauh lebih tebal?',
                'sop_steps'    => [
                    'Amati aliran sel darah biru (miskin O₂) menuju paru-paru dan darah merah (kaya O₂) menuju seluruh tubuh.',
                    'Ubah aktivitas dari Istirahat (72 BPM) ke Lari Sprint (155 BPM) dan catat peningkatan curah jantung.',
                    'Klik Uji Kasus untuk memahami mengapa dinding otot Bilik Kiri jauh lebih tebal.',
                ],
            ],
            [
                'slug'         => 'fisika-archimedes-apung',
                'title'        => 'Simulator Fisika: Misteri Kapal Baja & Hukum Archimedes',
                'category'     => 'smp',
                'subject_name' => 'Fisika / IPA (SMP & SMA)',
                'engine_type'  => 'physics_archimedes',
                'icon'         => 'fas fa-ship',
                'accent_color' => '#0284c7',
                'description'  => 'Mengapa paku besi kecil langsung tenggelam di air, sedangkan kapal baja ribuan ton justru bisa mengapung megah di lautan?',
                'sop_steps'    => [
                    'Celupkan Besi Pejal ke dalam tangki air dan amati mengapa gaya berat (W) mengalahkan gaya apung (Fa).',
                    'Ganti objek ke Kapal Baja Berongga dan perhatikan bagaimana rongga udara menurunkan massa jenis rata-rata.',
                    'Geser massa jenis zat cair (Minyak vs Air Tawar vs Air Laut) dan amati perubahan tinggi bagian yang tercelup.',
                ],
            ],
            [
                'slug'         => 'kimia-struktur-atom',
                'title'        => 'Simulator Kimia: Model Atom Bohr & Ikatan Garam (NaCl)',
                'category'     => 'sma',
                'subject_name' => 'Kimia (SMA)',
                'engine_type'  => 'chem_atom',
                'icon'         => 'fas fa-atom',
                'accent_color' => '#7c3aed',
                'description'  => 'Mengapa logam Natrium yang meledak di air dan gas Klorin beracun justru bersatu membentuk kristal garam dapur yang aman dimakan?',
                'sop_steps'    => [
                    'Pilih atom Karbon, Oksigen, Natrium, dan Klorin untuk mengamati elektron valensi di kulit terluar.',
                    'Klik mode Ikatan Ion Na⁺ + Cl⁻ untuk melihat perpindahan 1 elektron valensi dari Natrium ke Klorin.',
                    'Pastikan kedua ion mencapai konfigurasi stabil oktet (8 elektron di kulit terluar).',
                ],
            ],
            [
                'slug'         => 'ipa-efek-rumah-kaca',
                'title'        => 'Simulator IPA & Geografi: Efek Rumah Kaca & Iklim Bumi',
                'category'     => 'umum',
                'subject_name' => 'IPA / Geografi (SMP & SMA)',
                'engine_type'  => 'ipa_greenhouse',
                'icon'         => 'fas fa-earth-asia',
                'accent_color' => '#059669',
                'description'  => 'Mengapa suhu Bumi tetap hangat di malam hari, dan apa bahayanya jika gas karbon dioksida (CO₂) terus menebal di atmosfer?',
                'sop_steps'    => [
                    'Amati sinar tampak Matahari (kuning) menembus atmosfer dan memantul kembali sebagai panas inframerah (merah).',
                    'Naikkan kadar gas CO₂ dari 420 ppm ke 850 ppm dan perhatikan makin banyaknya panas merah yang terperangkap.',
                    'Catat kenaikan suhu rata-rata Bumi dan hubungkan dengan fenomena pemanasan global.',
                ],
            ],
            [
                'slug'         => 'lab-5-soal-olimpiade-internasional',
                'title'        => '🏆 Lab Tantangan 5 Soal Olimpiade Sains Internasional',
                'category'     => 'sma',
                'subject_name' => 'Olimpiade Sains (IPhO, IMO, IChO, IBO, IOI)',
                'engine_type'  => 'olympiad_5',
                'icon'         => 'fas fa-trophy',
                'accent_color' => '#d97706',
                'description'  => 'Mengapa bola tenis di atas bola basket memantul 9× lebih tinggi? Mengapa cukup 23 orang untuk peluang >50% ulang tahun sama? Pecahkan 5 soal Olimpiade Dunia secara visual!',
                'sop_steps'    => [
                    'Pilih satu per satu dari 5 tab soal Olimpiade (Fisika IPhO, Matematika IMO, Kimia IChO, Biologi IBO, Informatika IOI).',
                    'Baca pertanyaan misterinya, ubah slider/jalankan simulasi untuk membuktikan fenomenanya secara langsung.',
                    'Bandingkan hasil simulasi dengan langkah pembuktian matematis/logika di panel pembahasan.',
                ],
            ],
            [
                'slug'         => 'fisika-efek-doppler',
                'title'        => 'Simulator Fisika: Efek Doppler & Sirine Ambulans (Soal HOTS)',
                'category'     => 'sma',
                'subject_name' => 'Fisika (SMA / UTBK)',
                'engine_type'  => 'physics_doppler',
                'icon'         => 'fas fa-truck-medical',
                'accent_color' => '#ef4444',
                'description'  => 'Mengapa suara sirine ambulans terdengar melengking tinggi saat mendekat, lalu tiba-tiba merendah saat menjauh melewati kita?',
                'sop_steps'    => [
                    'Jalankan mobil ambulans dan amati lingkaran gelombang suara yang merapat di depan mobil dan meregang di belakang.',
                    'Ubah kecepatan ambulans dan amati perubahan frekuensi terdengar (f_p) saat mendekat vs menjauh.',
                    'Pecahkan Soal HOTS Efek Doppler di panel Tantangan Soal untuk memverifikasi pemahaman rumus.',
                ],
            ],
            [
                'slug'         => 'fisika-katrol-usaha',
                'title'        => 'Simulator Fisika: Katrol Majemuk & Gaya Kuasa (Soal HOTS)',
                'category'     => 'smp',
                'subject_name' => 'Fisika / IPA (SMP & SMA)',
                'engine_type'  => 'physics_pulley',
                'icon'         => 'fas fa-weight-hanging',
                'accent_color' => '#0284c7',
                'description'  => 'Bagaimana seorang pekerja pelabuhan sanggup mengangkat peti kemas seberat ratusan kilogram hanya dengan menarik tali katrol menggunakan satu tangan?',
                'sop_steps'    => [
                    'Bandingkan katrol tetap (1 tali, KM = 1) dengan sistem takal majemuk (2, 4, atau 6 tali penopang).',
                    'Amati hubungan antara berkurangnya Gaya Kuasa (F) dengan bertambahnya panjang tali yang harus ditarik.',
                    'Uji Soal HOTS Katrol di panel Tantangan Soal.',
                ],
            ],
            [
                'slug'         => 'kimia-stoikiometri-pembatas',
                'title'        => 'Simulator Kimia: Stoikiometri & Pereaksi Pembatas (Soal HOTS)',
                'category'     => 'sma',
                'subject_name' => 'Kimia (SMA / UTBK)',
                'engine_type'  => 'chem_stoichiometry',
                'icon'         => 'fas fa-vials',
                'accent_color' => '#8b5cf6',
                'description'  => 'Jika 6 molekul Hidrogen dicampur 2 molekul Oksigen untuk membuat air (2H₂ + O₂ → 2H₂O), mengapa ada zat yang bersisa dan tidak ikut bereaksi?',
                'sop_steps'    => [
                    'Atur jumlah awal mol H₂ dan mol O₂ lalu klik Mulai Reaksi.',
                    'Amati mana molekul yang habis terlebih dahulu (Pereaksi Pembatas) dan berapa molekul yang bersisa.',
                    'Selesaikan Soal HOTS Pereaksi Pembatas di panel Tantangan Soal.',
                ],
            ],
            [
                'slug'         => 'matematika-peluang-kombinasi',
                'title'        => 'Simulator Matematika: Peluang Dadu & Hukum Bilangan Besar',
                'category'     => 'smp',
                'subject_name' => 'Matematika (SMP / SMA / UTBK)',
                'engine_type'  => 'math_probability',
                'icon'         => 'fas fa-dice',
                'accent_color' => '#10b981',
                'description'  => 'Mengapa jumlah mata dadu 7 paling sering muncul saat dua dadu dilempar ratusan kali, sedangkan jumlah 2 atau 12 sangat jarang?',
                'sop_steps'    => [
                    'Lempar 2 dadu sekali dan catat jumlah mata dadunya.',
                    'Klik Lempar 500× Sekaligus untuk membuktikan grafik distribusi peluang berbentuk segitiga simetris memuncak di angka 7.',
                    'Selesaikan Soal Peluang Dua Dadu di panel Tantangan Soal.',
                ],
            ],
            [
                'slug'         => 'fisika-optika-lensa',
                'title'        => 'Simulator Fisika: Optika Lensa Cembung & Cekung (Pembentukan Bayangan)',
                'category'     => 'sma',
                'subject_name' => 'Fisika (SMA / UTBK)',
                'engine_type'  => 'physics_optics',
                'icon'         => 'fas fa-eye',
                'accent_color' => '#0284c7',
                'description'  => 'Bayangan maya atau nyata? Diperbesar atau diperkecil? Buktikan secara visual dengan rumus lensa 1/f = 1/s + 1/s\' dan sinar-sinar istimewa lensa cembung & cekung.',
                'sop_steps'    => [
                    'Pilih jenis lensa (Cembung + atau Cekung -) dan atur jarak fokus f.',
                    'Pindahkan benda dan amati perubahan posisi, ukuran, dan sifat bayangan secara real-time.',
                    'Selesaikan Soal HOTS Optika Lensa di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'fisika-newton-lift',
                'title'        => 'Simulator Fisika: Hukum Newton di Dalam Lift (Berat Semu)',
                'category'     => 'sma',
                'subject_name' => 'Fisika / IPA (SMP & SMA)',
                'engine_type'  => 'physics_elevator',
                'icon'         => 'fas fa-elevator',
                'accent_color' => '#0284c7',
                'description'  => 'Mengapa badan terasa berat saat lift naik dengan cepat, dan terasa ringan saat lift berhenti dari gerakan naik? Simulasikan Hukum Newton II pada gerak vertikal!',
                'sop_steps'    => [
                    'Atur massa orang dan percepatan lift, lalu pilih kondisi (Naik, Turun, Diam, Berhenti Naik).',
                    'Amati panah vektor gaya berat (W) dan gaya normal (N) yang berubah sesuai kondisi.',
                    'Selesaikan Soal HOTS Berat Semu di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'fisika-medan-listrik',
                'title'        => 'Simulator Fisika: Medan Listrik & Gaya Coulomb (Visualisasi 2D)',
                'category'     => 'sma',
                'subject_name' => 'Fisika (SMA / UTBK)',
                'engine_type'  => 'physics_electric_field',
                'icon'         => 'fas fa-bolt',
                'accent_color' => '#eab308',
                'description'  => 'Klik kanvas untuk menempatkan muatan positif dan negatif, lalu saksikan garis-garis medan listrik terbentuk real-time dan hitung gaya Coulomb antar muatan!',
                'sop_steps'    => [
                    'Tempatkan muatan positif (+q) dan negatif (-q) di kanvas, amati garis medan yang saling tarik.',
                    'Ubah besar muatan (Q) dan jarak (r) pada kalkulator Coulomb di panel bawah.',
                    'Selesaikan Soal HOTS Gaya Coulomb di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'biologi-osmosis-sel',
                'title'        => 'Simulator Biologi: Osmosis, Difusi & Tekanan Osmotik Sel',
                'category'     => 'sma',
                'subject_name' => 'Biologi / IPA (SMP & SMA)',
                'engine_type'  => 'bio_osmosis',
                'icon'         => 'fas fa-droplet',
                'accent_color' => '#059669',
                'description'  => 'Mengapa sel wortel yang direndam garam bisa mengkerut (plasmolisis), sedangkan yang direndam air tawar mengembang (turgid)? Eksperimen osmosis sel secara visual!',
                'sop_steps'    => [
                    'Pilih kondisi Hipotonik (air tawar), Isotonik, atau Hipertonik (garam) dan amati arah pergerakan air.',
                    'Atur konsentrasi larutan luar dan catat perubahan ukuran sel serta arah panah osmosis.',
                    'Selesaikan Soal HOTS Osmosis Sel di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'biologi-sistem-saraf',
                'title'        => 'Simulator Biologi: Sistem Saraf & Penjalaran Impuls Neuron',
                'category'     => 'sma',
                'subject_name' => 'Biologi / IPA (SMP & SMA)',
                'engine_type'  => 'bio_nervous',
                'icon'         => 'fas fa-brain',
                'accent_color' => '#7c3aed',
                'description'  => 'Bagaimana sinyal listrik (impuls saraf) merambat dari reseptor → neuron → otak dalam hitungan milidetik? Dan mengapa gerak refleks terjadi TANPA melewati otak?',
                'sop_steps'    => [
                    'Klik "Kirim Impuls" untuk melihat perambatan impuls biasa melewati otak.',
                    'Klik "Gerak Refleks" untuk melihat jalur singkat via sumsum tulang belakang — jauh lebih cepat!',
                    'Selesaikan Soal HOTS Sistem Saraf di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'matematika-integral-riemann',
                'title'        => 'Simulator Matematika: Integral Riemann Visual (Luas di Bawah Kurva)',
                'category'     => 'sma',
                'subject_name' => 'Matematika (SMA / UTBK)',
                'engine_type'  => 'math_integral',
                'icon'         => 'fas fa-chart-area',
                'accent_color' => '#4f46e5',
                'description'  => 'Bagaimana menghitung luas area di bawah kurva menggunakan jumlah tak terbatas persegi panjang tipis? Visualisasikan aproksimasi Riemann dan bandingkan dengan nilai eksak!',
                'sop_steps'    => [
                    'Pilih fungsi f(x) dan atur batas integral (a dan b).',
                    'Naikkan jumlah partisi (n) dari 2 hingga 200 dan lihat persegi makin mengisi kurva sempurna.',
                    'Selesaikan Soal HOTS Integral Pasti di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'matematika-trigonometri-interaktif',
                'title'        => 'Simulator Matematika: Fungsi Trigonometri sin, cos, tan (Lingkaran Satuan)',
                'category'     => 'sma',
                'subject_name' => 'Matematika (SMA / SMK / UTBK)',
                'engine_type'  => 'math_trig_circle',
                'icon'         => 'fas fa-circle-notch',
                'accent_color' => '#4f46e5',
                'description'  => 'Geser titik pada lingkaran satuan dan saksikan gelombang sin, cos, tan terbentuk real-time! Pahami hubungan antara sudut, koordinat, dan nilai trigonometri secara visual.',
                'sop_steps'    => [
                    'Geser slider sudut θ dari 0° hingga 360° dan amati titik yang bergerak di lingkaran satuan.',
                    'Pilih tampilan sin, cos, tan, atau ketiganya sekaligus untuk membandingkan gelombang.',
                    'Selesaikan Soal HOTS Trigonometri (identitas sin(A+B)) di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'vokasi-jaringan-lan',
                'title'        => 'Simulator TKJ: Topologi Jaringan LAN & Simulasi Paket Data',
                'category'     => 'vokasi',
                'subject_name' => 'Teknik Komputer & Jaringan (TKJ / ACP)',
                'engine_type'  => 'vokasi_lan_topo',
                'icon'         => 'fas fa-network-wired',
                'accent_color' => '#0ea5e9',
                'description'  => 'Bandingkan 4 topologi jaringan LAN (Bus, Star, Ring, Mesh), hitung jumlah link yang dibutuhkan, dan simulasikan pengiriman paket data antar host dengan klik!',
                'sop_steps'    => [
                    'Pilih topologi jaringan (Bus, Star, Ring, atau Mesh) dan atur jumlah host.',
                    'Klik node pengirim, lalu klik node tujuan — amati paket data melompat antar switch.',
                    'Selesaikan Soal HOTS Topologi Mesh di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'vokasi-cad-denah',
                'title'        => 'Simulator DPIB: CAD Denah & Struktur Bangunan',
                'category'     => 'vokasi',
                'subject_name' => 'Desain Pemodelan & Info Bangunan (DPIB)',
                'engine_type'  => 'vokasi_cad_dpib',
                'icon'         => 'fas fa-drafting-compass',
                'accent_color' => '#0ea5e9',
                'description'  => 'Gambar denah ruangan dengan alat dinding, pintu, jendela, dan ruangan. Hitung luas otomatis dengan grid 1m × 1m dan pelajari prinsip skala gambar 1:100!',
                'sop_steps'    => [
                    'Pilih alat Dinding atau Ruangan, lalu klik dan seret di kanvas untuk menggambar.',
                    'Tambahkan Pintu dan Jendela, lalu amati kalkulasi luas otomatis di panel kiri.',
                    'Klik "Contoh Denah" untuk melihat contoh denah rumah 3 ruangan dan selesaikan Soal Skala.',
                ],
            ],
            [
                'slug'         => 'ips-inflasi-deflasi',
                'title'        => 'Simulator IPS/Ekonomi: Inflasi, Deflasi & Daya Beli Masyarakat',
                'category'     => 'sma',
                'subject_name' => 'Ekonomi / IPS (SMP & SMA)',
                'engine_type'  => 'econ_inflation',
                'icon'         => 'fas fa-chart-line',
                'accent_color' => '#7c3aed',
                'description'  => 'Mengapa harga barang terus naik setiap tahun? Simulasikan dampak inflasi, hiperinflasi, dan deflasi terhadap harga beras dan daya beli riil masyarakat selama 30 tahun!',
                'sop_steps'    => [
                    'Pilih skenario Inflasi Normal (5%), Hiperinflasi (50%), atau Deflasi (-3%).',
                    'Atur gaji awal dan proyeksikan ke depan untuk melihat grafik daya beli riil vs tingkat harga.',
                    'Selesaikan Soal HOTS Daya Beli Inflasi di panel tantangan soal.',
                ],
            ],
            [
                'slug'         => 'ptn-soal-cepat-lanjutan',
                'title'        => '⚡ Simulator 20 Soal Cepat SNBT/UTBK Lanjutan (Kimia · Bio · B.Indo · B.Ing · Sosiologi)',
                'category'     => 'sma',
                'subject_name' => 'Persiapan PTN / SNBT / UTBK (Lintas Ilmu Lanjutan)',
                'engine_type'  => 'ptn_speed_advanced',
                'icon'         => 'fas fa-graduation-cap',
                'accent_color' => '#2563eb',
                'description'  => '20 soal SNBT/UTBK pilihan mencakup Kimia, Biologi, Bahasa Indonesia, Bahasa Inggris, Sosiologi, Geografi, dan Penalaran Matematis — dengan trik penyelesaian super cepat!',
                'sop_steps'    => [
                    'Jawab setiap soal dalam batas waktu 45 detik — tekan tombol huruf pilihan Anda.',
                    'Klik "Lihat Trik" untuk melihat cara cepat penyelesaian kurang dari 15 detik.',
                    'Targetkan skor minimum 75% (15/20) untuk persiapan PTN Favorit.',
                ],
            ],
            [
                'slug'         => 'ptn-soal-cepat-snbt',
                'title'        => '⚡ Simulator Penyelesaian Soal Cepat PTN Favorit (15 Soal SNBT / UTBK)',
                'category'     => 'sma',
                'subject_name' => 'Persiapan PTN / SNBT / UTBK (Lintas Ilmu)',
                'engine_type'  => 'ptn_speed',
                'icon'         => 'fas fa-bolt',
                'accent_color' => '#2563eb',
                'description'  => 'Mengapa siswa sering kehabisan waktu saat UTBK? Latih trik penyelesaian < 15 detik untuk 15 soal wajib SNBT/UTBK (Kuantitatif, Aljabar, Geometri, Fisika, Kimia, Biologi, Ekonomi & Logika)!',
                'sop_steps'    => [
                    'Pilih 15 topik soal yang paling sering keluar di tes PTN Favorit.',
                    'Perhatikan stopwatch di kanan atas dan coba jawab secepat mungkin (< 45 detik).',
                    'Klik Bongkar Visualisasi Pola & Trik Super Cepat untuk membandingkan cara panjang vs trik 15 detik.',
                ],
            ],
            [
                'slug'         => 'ipa-energi-terbarukan',
                'title'        => 'Simulator IPA & Teknologi: Panel Surya & Turbin Angin',
                'category'     => 'sma',
                'subject_name' => 'IPA / Fisika / Teknologi Hijau',
                'engine_type'  => 'ipa_renewable',
                'icon'         => 'fas fa-solar-panel',
                'accent_color' => '#0284c7',
                'description'  => 'Bagaimana sinar matahari dan angin diubah menjadi energi listrik (Watt) untuk menyalakan rumah modern masa depan?',
                'sop_steps'    => [
                    'Atur intensitas cahaya matahari dan sudut kemiringan panel surya untuk mencari daya maksimum.',
                    'Naikkan kecepatan angin dan amati peningkatan daya turbin angin secara kubik (v³).',
                    'Amati nyala lampu rumah seiring bertambahnya total daya listrik terbarukan.',
                ],
            ],
            [
                'slug'         => 'biologi-genetika-mendel',
                'title'        => 'Simulator Biologi: Genetika Mendel & Papan Punnett',
                'category'     => 'sma',
                'subject_name' => 'Biologi (SMP & SMA)',
                'engine_type'  => 'bio_genetics',
                'icon'         => 'fas fa-seedling',
                'accent_color' => '#059669',
                'description'  => 'Mengapa dua induk tanaman tinggi (Aa × Aa) tetap bisa melahirkan keturunan bertubuh pendek (aa)? Uji persilangannya di Papan Punnett!',
                'sop_steps'    => [
                    'Pilih genotipe Induk 1 dan Induk 2 (AA, Aa, atau aa).',
                    'Amati kombinasi gamet pada 4 kotak Papan Punnett.',
                    'Catat rasio genotipe dan rasio fenotipe dominan vs resesif yang terbentuk.',
                ],
            ],
            [
                'slug'         => 'matematika-transformasi-geometri',
                'title'        => 'Simulator Matematika: Transformasi Geometri 2D (Grafika & Robotika)',
                'category'     => 'sma',
                'subject_name' => 'Matematika / Informatika (SMP & SMA)',
                'engine_type'  => 'math_transform',
                'icon'         => 'fas fa-vector-square',
                'accent_color' => '#4f46e5',
                'description'  => 'Bagaimana komputer menggeser, memutar, dan memperbesar objek dalam game atau robotika menggunakan Translasi, Rotasi & Dilatasi?',
                'sop_steps'    => [
                    'Geser slider Translasi X dan Y untuk memindahkan posisi segitiga bayangan.',
                    'Putar slider Rotasi Sudut (0°–360°) dan amati koordinat bayangan P1\', P2\', P3\'.',
                    'Ubah Skala Dilatasi (k) untuk memperbesar atau memperkecil bangun.',
                ],
            ],
            [
                'slug'         => 'vokasi-otomasi-relay',
                'title'        => 'Simulator Vokasi SMK: Kontrol Relay Self-Holding & Motor Industri',
                'category'     => 'vokasi',
                'subject_name' => 'Teknik Listrik / Otomasi / Industri (SMK)',
                'engine_type'  => 'vokasi_plc',
                'icon'         => 'fas fa-industry',
                'accent_color' => '#ea580c',
                'description'  => 'Bagaimana tombol START sesaat bisa membuat motor pabrik terus berputar hingga tombol STOP atau Emergency Stop ditekan?',
                'sop_steps'    => [
                    'Tekan Push Button START (NO) untuk mengaktifkan Kontaktor K1 dan memutar motor 3-fasa.',
                    'Uji tombol STOP (NC) untuk memutus rangkaian pengunci (self-holding).',
                    'Uji EMERGENCY STOP dan amati penguncian pengaman hingga di-reset.',
                ],
            ],
            [
                'slug'         => 'ekonomi-bunga-majemuk',
                'title'        => 'Simulator Literasi Keuangan: Bunga Majemuk & Investasi Karier',
                'category'     => 'umum',
                'subject_name' => 'Ekonomi / Matematika Keuangan',
                'engine_type'  => 'econ_compound',
                'icon'         => 'fas fa-piggy-bank',
                'accent_color' => '#7c3aed',
                'description'  => 'Mengapa menabung/berinvestasi sejak muda menghasilkan selisih kekayaan berlipat ganda dibanding menunggu tua? Bandingkan Bunga Majemuk vs Bunga Tunggal!',
                'sop_steps'    => [
                    'Atur modal awal, tingkat imbal hasil tahunan (%), dan lama waktu investasi (tahun).',
                    'Bandingkan kurva biru (Bunga Tunggal lurus) dengan kurva hijau (Bunga Majemuk eksponensial).',
                    'Amati lonjakan nilai investasi setelah melewati tahun ke-15.',
                ],
            ],
        ];

        foreach ($defaults as $item) {
            static::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
