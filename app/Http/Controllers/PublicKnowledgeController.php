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
     * Download Official Journal Template
     */
    public function downloadTemplate()
    {
        $content = "=========================================================================\n"
                 . " PERGURUAN PEMBDA - TEMPLATE RESMI JURNAL & PUBLIKASI CIVITAS AKADEMIKA\n"
                 . "=========================================================================\n\n"
                 . "[JUDUL JURNAL / ARTIKEL / KARYA TULIS ILMIAH]\n"
                 . "Nama Penulis : [Nama Lengkap Penulis, Gelar]\n"
                 . "Jabatan/Unit : Civitas Akademika Perguruan Pembda\n"
                 . "Kontak/Email : [email@perguruanpembda.com]\n\n"
                 . "ABSTRAK\n"
                 . "-------------------------------------------------------------------------\n"
                 . "Tuliskan ringkasan singkat abstrak karya tulis Anda di sini (150-250 kata). "
                 . "Mencakup latar belakang, tujuan penulisan, metode/pendekatan, serta hasil atau kesimpulan utama.\n\n"
                 . "Kata Kunci: Kata Kunci 1, Kata Kunci 2, Kata Kunci 3\n\n"
                 . "1. PENDAHULUAN\n"
                 . "-------------------------------------------------------------------------\n"
                 . "Tuliskan latar belakang masalah, urgensi topik, referensi pendukung, dan tujuan karya tulis ini...\n\n"
                 . "2. METODE & PEMBAHASAN URAIAN MATERI\n"
                 . "-------------------------------------------------------------------------\n"
                 . "Uraikan metode pembelajaran, konsep, analisis, atau materi inti yang dibahas secara sistematis...\n\n"
                 . "3. HASIL DAN KESIMPULAN\n"
                 . "-------------------------------------------------------------------------\n"
                 . "Tuliskan kesimpulan akhir, rekomendasi, dan penutup...\n\n"
                 . "DAFTAR PUSTAKA / REFERENSI\n"
                 . "-------------------------------------------------------------------------\n"
                 . "1. Nama Penulis. (Tahun). Judul Referensi. Penerbit/Jurnal.\n\n"
                 . "* Catatan: Penggunaan template ini sifatnya OPSIONAL (Bebas / Tidak Wajib).\n"
                 . "Guru & Civitas Akademika diperbolehkan mengunggah karya dengan format penulisan mandiri.";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="TEMPLATE_JURNAL_KARYA_TULIS_PEMBDA.txt"',
        ]);
    }
}
