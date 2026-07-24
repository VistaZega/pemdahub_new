<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBookmark;
use App\Models\KnowledgeLike;
use App\Models\KnowledgeMaterial;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PublicKnowledgeController extends Controller
{
    /**
     * Display public catalog of Pembda Knowledge & Media
     */
    public function index(Request $request)
    {
        $query = KnowledgeMaterial::with(['teacher', 'subject'])
            ->where('is_public', true);

        // Filter: Category Type
        if ($request->filled('category')) {
            $query->where('category_type', $request->category);
        }

        // Filter: Media Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter: Subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter: Teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Search Query
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $materials = $query->latest()->paginate(12)->withQueryString();

        $subjects = Subject::where('is_active', true)->orderBy('subject_name')->get();
        $teachers = Teacher::orderBy('full_name')->get();

        // Check user bookmarks if logged in
        $userBookmarks = [];
        $userLikes = [];
        if (Auth::check()) {
            $userId = Auth::id();
            $userBookmarks = KnowledgeBookmark::where('user_id', $userId)->pluck('knowledge_material_id')->toArray();
            $userLikes = KnowledgeLike::where('user_id', $userId)->pluck('knowledge_material_id')->toArray();
        }

        return view('public.knowledge.index', compact(
            'materials',
            'subjects',
            'teachers',
            'userBookmarks',
            'userLikes'
        ));
    }

    /**
     * Show detailed material viewer
     */
    public function show($slug)
    {
        $material = KnowledgeMaterial::with(['teacher', 'subject'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment Views
        $material->increment('views_count');

        // Check user interactions
        $isLiked = false;
        $isBookmarked = false;

        if (Auth::check()) {
            $userId = Auth::id();
            $isLiked = KnowledgeLike::where('knowledge_material_id', $material->id)->where('user_id', $userId)->exists();
            $isBookmarked = KnowledgeBookmark::where('knowledge_material_id', $material->id)->where('user_id', $userId)->exists();
        }

        // Generate Share URLs
        $shareUrl = route('knowledge.show', $material->slug);
        $waShareUrl = "https://api.whatsapp.com/send?text=" . urlencode("Lihat materi *" . $material->title . "* karya " . ($material->teacher ? $material->teacher->full_name : 'Guru Pembda') . " di Pembda Knowledge & Media:\n" . $shareUrl);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($shareUrl);

        return view('public.knowledge.show', compact(
            'material',
            'isLiked',
            'isBookmarked',
            'shareUrl',
            'waShareUrl',
            'qrCodeUrl'
        ));
    }

    /**
     * Toggle Like
     */
    public function toggleLike(KnowledgeMaterial $knowledge)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Silakan login terlebih dahulu untuk menyukai materi.'], 401);
        }

        $userId = Auth::id();
        $existing = KnowledgeLike::where('knowledge_material_id', $knowledge->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $knowledge->decrement('likes_count');
            $liked = false;
        } else {
            KnowledgeLike::create([
                'knowledge_material_id' => $knowledge->id,
                'user_id' => $userId,
                'ip_address' => request()->ip(),
            ]);
            $knowledge->increment('likes_count');
            $liked = true;
        }

        $knowledge->refresh();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $knowledge->likes_count,
        ]);
    }

    /**
     * Toggle Bookmark
     */
    public function toggleBookmark(KnowledgeMaterial $knowledge)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Silakan login terlebih dahulu untuk menyimpan favorit.'], 401);
        }

        $userId = Auth::id();
        $existing = KnowledgeBookmark::where('knowledge_material_id', $knowledge->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $knowledge->decrement('bookmarks_count');
            $bookmarked = false;
        } else {
            KnowledgeBookmark::create([
                'knowledge_material_id' => $knowledge->id,
                'user_id' => $userId,
            ]);
            $knowledge->increment('bookmarks_count');
            $bookmarked = true;
        }

        $knowledge->refresh();

        return response()->json([
            'success' => true,
            'bookmarked' => $bookmarked,
            'bookmarks_count' => $knowledge->bookmarks_count,
        ]);
    }

    /**
     * Secure Download Material
     */
    public function download(KnowledgeMaterial $knowledge)
    {
        if (!$knowledge->allow_download) {
            return back()->with('error', 'Guru pemilik materi ini tidak mengizinkan opsi unduh.');
        }

        if (!$knowledge->file_path || !Storage::disk('public')->exists($knowledge->file_path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        $knowledge->increment('downloads_count');

        return Storage::disk('public')->download($knowledge->file_path, $knowledge->title . '.' . pathinfo($knowledge->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Download Official International-Standard Journal Template
     */
    public function downloadTemplate()
    {
        $content = "========================================================================================================\n"
                 . " JURNAL EDUSAINS & TEKNOLOGI PEMBDA (JET-PEMBDA)\n"
                 . " YAYASAN PERGURUAN PEMBDA NIAS (PEMBDA) | Dipublikasikan di PembdaHUB\n"
                 . " ISSN (Online): 2988-7123 | Vol. 1, No. 1, Juli 2026 | https://perguruanpembda.com/knowledge\n"
                 . "========================================================================================================\n\n"
                 . "[JUDUL ARTIKEL JURNAL SINGKAT, JELAS, DAN INFORMATIF - MAKSIMAL 15 KATA]\n"
                 . "[ENGLISH TITLE: CLEAR, CONCISE, AND INFORMATIVE ARTICLE TITLE - MAXIMUM 15 WORDS]\n\n"
                 . "Nama Penulis Pertama 1*, Nama Penulis Kedua 2, Nama Penulis Ketiga 3\n"
                 . "1,2,3 Unit Kerja / Mata Pelajaran / Program Studi, Yayasan Perguruan Pembda Nias, Indonesia\n"
                 . "*Email Penulis Korespondensi: penulis.utama@perguruanpembda.com\n\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "ABSTRAK (Bahasa Indonesia)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Tuliskan abstrak berbahasa Indonesia di sini (150 - 250 kata). Abstrak harus merangkum secara utuh "
                 . "isi artikel jurnal, mencakup: (1) Latar belakang ringkas & tujuan utama inovasi/penelitian, "
                 . "(2) Metode atau pendekatan pembelajaran/pengembangan yang digunakan, (3) Temuan atau hasil utama "
                 . "penerapan karya, serta (4) Kesimpulan dan dampak positifnya bagi Civitas Akademika Perguruan Pembda Nias. "
                 . "Gunakan kalimat yang lugas, jelas, dan tanpa rujukan pustaka.\n\n"
                 . "Kata Kunci: PembdaHUB; Perguruan Pembda Nias; Inovasi Pembelajaran; Mikrokontroler; Literasi Digital.\n\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "ABSTRACT (English)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Write the English abstract here (150 - 250 words). The abstract must provide a complete summary of "
                 . "the article, including: (1) Background and primary objectives, (2) Methods or pedagogical approaches "
                 . "applied, (3) Key findings and results, and (4) Main conclusion and significance for the academic "
                 . "community of Yayasan Perguruan Pembda Nias. Use clear and concise language.\n\n"
                 . "Keywords: PembdaHUB; Perguruan Pembda Nias; Educational Innovation; Microcontroller; Digital Literacy.\n\n"
                 . "========================================================================================================\n"
                 . "I. PENDAHULUAN (INTRODUCTION)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Pendahuluan menguraikan latar belakang masalah, konteks pembelajaran di lingkungan Yayasan Perguruan "
                 . "Pembda Nias, urgensi topik yang dibahas, serta kebaruan (novelty) atau gagasan inovatif yang ditawarkan. "
                 . "Sebutkan studi/literatur terdahulu yang relevan dan akhiri bagian ini dengan tujuan penulisan karya.\n\n"
                 . "II. METODE PENELITIAN & IMPLEMENTASI PEMBELAJARAN (METHODS & IMPLEMENTATION)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Jelaskan metode, pendekatan, instrumen, subjek/siswa sasaran, serta prosedur pelaksanaan penelitian "
                 . "atau implementasi modul pembelajaran secara terstruktur. Apabila menggunakan media digital PembdaHUB, "
                 . "paparkan tahapan integrasi media tersebut dalam Kegiatan Belajar Mengajar (KBM).\n\n"
                 . "III. HASIL DAN PEMBAHASAN (RESULTS AND DISCUSSION)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Sajikan data hasil penerapan, analisis pencapaian siswa/civitas akademika, data statistik interaksi, "
                 . "tabel hasil pengukuran, serta pembahasan mendalam. Bandingkan hasil karya Anda dengan teori atau "
                 . "penelitian terdahulu untuk menunjukkan dampak keberhasilannya.\n\n"
                 . "IV. KESIMPULAN DAN SARAN (CONCLUSION AND RECOMMENDATIONS)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Kemukakan kesimpulan utama yang menjawab tujuan penelitian/karya tulis. Berikan saran praktis atau "
                 . "rekomendasi tindak lanjut bagi guru, sekolah, dan pengembangan portal PembdaHUB ke depan.\n\n"
                 . "UCAPAN TERIMA KASIH (ACKNOWLEDGMENT)\n"
                 . "--------------------------------------------------------------------------------------------------------\n"
                 . "Penulis mengucapkan terima kasih kepada Pengurus Yayasan Perguruan Pembda Nias, Kepala Sekolah, "
                 . "rekan Guru Civitas Akademika, serta Pengembang PembdaHUB atas dukungan fasilitas dan sarana publikasi.\n\n"
                 . "========================================================================================================\n"
                 . "DAFTAR PUSTAKA / REFERENCES (Standar APA 7th Edition)\n"
                 . "========================================================================================================\n"
                 . "[REFERENSI LOKAL / NASIONAL - MINIMAL 5 PUSTAKA]\n"
                 . "1. Zega, Y., & Hia, T. (2025). Inovasi Pembelajaran Digital Berbasis Mikrokontroler dan IoT pada Sekolah Menengah di Nias. Jurnal Pendidikan Teknologi & Vokasi Pembda, 4(1), 12-25.\n"
                 . "2. Kemendikbudristek. (2024). Panduan Transformasi Digital dan Pembelajaran Interaktif di Satuan Pendidikan. Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi Republik Indonesia.\n"
                 . "3. Harefa, D., & Lase, A. (2024). Penerapan Modul Digital PembdaHUB untuk Meningkatkan Literasi Sains dan Teknologi Siswa. Jurnal Ilmiah Pendidikan Indonesia, 10(2), 88-97.\n"
                 . "4. Telaumbanua, K. (2023). Pengembangan Media Pembelajaran Interaktif Berbasis Web di Wilayah Kepulauan Nias. Jurnal Teknologi Pendidikan Nasional, 8(3), 145-156.\n"
                 . "5. Yulianus, Z., & Tim PembdaHUB. (2026). Portal PembdaHUB: Integrasi LMS, CBT, dan Repositori Jurnal Civitas Akademika. Jurnal Sains & Aplikasi Pembda, 2(1), 1-15.\n\n"
                 . "[REFERENSI INTERNASIONAL - MINIMAL 3 PUSTAKA]\n"
                 . "6. UNESCO. (2024). Global Education Monitoring Report: Technology in Education – A Tool on Whose Terms? UNESCO Publishing, Paris.\n"
                 . "7. Siemens, G., & Downes, S. (2023). Connectivism and Digital Pedagogy in Modern K-12 and Higher Education Environments. IEEE Transactions on Learning Technologies, 16(4), 410-422.\n"
                 . "8. Mayer, R. E. (2022). The Cambridge Handbook of Multimedia Learning (3rd ed.). Cambridge University Press. https://doi.org/10.1017/9781108894333\n\n"
                 . "========================================================================================================\n"
                 . "* CATATAN PENTING FORUM JURNAL:\n"
                 . "Penggunaan template ini sifatnya OPSIONAL (Bebas / Tidak Wajib).\n"
                 . "Guru & Civitas Akademika Yayasan Perguruan Pembda Nias diperbolehkan mengunggah karya tulis mandiri\n"
                 . "atau menggunakan format penulisan bebas sesuai kreatifitas pendidik.\n"
                 . "========================================================================================================";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="TEMPLATE_JURNAL_CIVITAS_AKADEMIKA_PEMBDA_NIAS.txt"',
        ]);
    }
}
