<?php

namespace App\Http\Controllers;

use App\Models\SimProject;
use App\Models\VirtualLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SimLabController extends Controller
{
    /**
     * Display SimLab Dashboard / Gallery
     */
    public function index()
    {
        $user = Auth::user();
        
        $myProjects = collect();
        if ($user) {
            $myProjects = SimProject::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        $templates = SimProject::where('is_template', true)
            ->orWhere(function ($q) {
                $q->where('is_public', true)->whereNull('user_id');
            })
            ->orderBy('title', 'asc')
            ->get();

        $virtualLabs = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('virtual_labs')) {
                VirtualLab::ensureDefaultLabs();
                $virtualLabs = VirtualLab::with('course')
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } catch (\Throwable $e) {
            // fallback jika migrasi belum dijalankan
        }

        $teacherCourses = collect();
        if ($user && $user->teacher) {
            $teacherCourses = \App\Models\LmsCourse::where('teacher_id', $user->teacher->id)
                ->orderByDesc('id')
                ->get(['id', 'course_name']);
        }

        return view('simlab.index', compact('myProjects', 'templates', 'virtualLabs', 'teacherCourses'));
    }

    /**
     * Display Interactive Virtual Lab Room (Animated Multi-Subject Lab + SOP)
     */
    public function lab($slug)
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('virtual_labs')) {
            VirtualLab::ensureDefaultLabs();
        }
        $lab = VirtualLab::with('course')->where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('simlab.lab', compact('lab'));
    }

    /**
     * Generate a brand new interactive HTML5/Canvas Simulator Lab using AI (for Teachers/Admins).
     */
    public function generateAiLab(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->teacher && !$user->isAdmin())) {
            return response()->json(['success' => false, 'message' => 'Hanya Guru atau Admin yang dapat membuat Simulator Lab baru.'], 403);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:150',
            'category'     => 'required|in:umum,smp,sma,vokasi',
            'subject_name' => 'required|string|max:120',
            'course_id'    => 'nullable|exists:lms_courses,id',
            'description'  => 'nullable|string|max:1000',
        ]);

        $prompt = "Anda adalah Ahli Desain Laboratorium Virtual Pendidikan (HTML5 Canvas + JavaScript).\n"
            . "Buatkan 1 halaman HTML5 lengkap mandiri (single-file HTML dengan inline <style> dan <script>, TANPA library eksternal) untuk Simulator Laboratorium Interaktif:\n"
            . "- Judul Lab: {$validated['title']}\n"
            . "- Mata Pelajaran / Kejuruan: {$validated['subject_name']}\n"
            . "- Tingkat/Kategori: {$validated['category']}\n"
            . "- Deskripsi Eksperimen: " . ($validated['description'] ?: 'Eksperimen interaktif dengan tuning variabel dan animasi visual.') . "\n\n"
            . "SYARAT WAJIB:\n"
            . "1. Harus memiliki <canvas> animasi berwarna yang bergerak halus (requestAnimationFrame) dan berubah secara real-time saat slider/tombol diubah.\n"
            . "2. Harus memiliki minimal 2-3 kontrol tuning variabel (slider <input type='range'> atau tombol toggle) beserta pembacaan nilai hasil/efek perubahan secara langsung.\n"
            . "3. Gunakan tema gelap modern (#0f172a background, teks putih/terang, aksen warna cerah #34d399, #fbbf24, #38bdf8) agar menyatu dengan Simulator Lab PembdaHUB.\n"
            . "4. Jangan gunakan markdown code fence (```html). Kembalikan HANYA kode HTML valid mulai dari <!DOCTYPE html> hingga </html>.";

        try {
            $gemini = app(\App\Services\GeminiService::class);
            $html = trim($gemini->generateText($prompt));
            foreach (['```html', '```'] as $fence) {
                if (str_starts_with($html, $fence)) {
                    $html = substr($html, strlen($fence));
                }
            }
            if (str_ends_with($html, '```')) {
                $html = substr($html, 0, -3);
            }
            $html = trim($html);

            if (!str_contains(strtolower($html), '<canvas') && !str_contains(strtolower($html), '<svg')) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI belum menghasilkan kanvas simulasi yang valid. Silakan coba lagi dengan deskripsi eksperimen yang lebih spesifik.',
                ], 422);
            }

            $slug = Str::slug($validated['title']) . '-' . Str::lower(Str::random(5));
            $lab = VirtualLab::create([
                'title'        => $validated['title'],
                'slug'         => $slug,
                'category'     => $validated['category'],
                'subject_name' => $validated['subject_name'],
                'engine_type'  => 'ai_custom_html',
                'description'  => $validated['description'] ?: "Simulator interaktif {$validated['subject_name']} yang dibangkitkan untuk membantu pemahaman visual siswa.",
                'sop_steps'    => [
                    'Baca tujuan eksperimen dan perhatikan variabel yang dapat diatur pada panel kontrol.',
                    'Atur nilai awal variabel menggunakan slider/tombol yang tersedia.',
                    'Amati perubahan animasi dan angka pembacaan secara real-time untuk menyimpulkan konsep.',
                ],
                'custom_html'  => $html,
                'course_id'    => $validated['course_id'] ?? null,
                'created_by'   => $user->id,
                'icon'         => 'fas fa-atom',
                'accent_color' => '#38bdf8',
                'is_active'    => true,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Simulator Lab baru berhasil dibuat!',
                'redirect_url' => route('simlab.lab', $lab->slug),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membangkitkan simulator AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a custom VirtualLab created by Teacher/Admin.
     */
    public function destroyLab($id)
    {
        $user = Auth::user();
        $lab = VirtualLab::findOrFail($id);
        if (!$user || ($lab->created_by !== $user->id && !$user->isAdmin())) {
            abort(403, 'Anda tidak berhak menghapus simulator ini.');
        }
        $lab->delete();
        return redirect()->route('simlab.index')->with('success', 'Simulator Lab berhasil dihapus.');
    }

    /**
     * Open SimLab Workspace Editor
     */
    public function editor($id = null)
    {
        $project = null;
        $templateKey = null;

        if ($id && $id !== 'new') {
            if (is_numeric($id)) {
                $project = SimProject::find($id);
            } else if (strlen($id) > 10) {
                $project = SimProject::where('share_token', $id)->first();
            }

            if (!$project) {
                $templateKey = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $id);
            }
        }

        return view('simlab.editor', compact('project', 'templateKey'));
    }

    /**
     * Save/Update SimLab Project via Ajax
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'board_type' => 'required|string|in:uno,nano,esp32',
            'circuit_json' => 'nullable',
            'code_ino' => 'nullable|string',
        ]);

        $user = Auth::user();

        $projectId = $request->input('id');
        $project = null;

        if ($projectId) {
            $project = SimProject::find($projectId);
            if ($project && $user && $project->user_id !== $user->id && !$user->isAdmin()) {
                // If user doesn't own it, fork it
                $project = null;
            }
        }

        if (!$project) {
            $project = new SimProject();
            $project->share_token = Str::random(12);
        }

        $project->title = $request->input('title');
        $project->description = $request->input('description', '');
        $project->board_type = $request->input('board_type', 'uno');
        $project->circuit_json = is_array($request->input('circuit_json')) 
            ? $request->input('circuit_json') 
            : json_decode($request->input('circuit_json'), true);
        $project->code_ino = $request->input('code_ino', '');
        
        if ($user) {
            $project->user_id = $user->id;
            $project->user_type = $user->role ?? 'guru';
        }

        $project->is_public = $request->boolean('is_public', true);
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Proyek SimLab berhasil disimpan!',
            'project' => $project,
            'redirect' => route('simlab.editor', $project->id),
        ]);
    }

    /**
     * Get Project JSON Data
     */
    public function show($id)
    {
        $project = SimProject::find($id);
        if (!$project) {
            $project = SimProject::where('share_token', $id)->firstOrFail();
        }

        return response()->json([
            'success' => true,
            'project' => $project,
        ]);
    }

    /**
     * Compile Arduino Code (.ino -> hex / syntax check)
     */
    public function compile(Request $request)
    {
        $code = $request->input('code', '');
        $board = $request->input('board', 'uno');

        if (empty(trim($code))) {
            return response()->json([
                'success' => false,
                'message' => 'Kode kosong. Tuliskan kode Arduino Anda terlebih dahulu.',
            ], 422);
        }

        // Perform fast syntax analysis & simulate compilation result
        // Check for basic C++ structure errors
        $errors = [];

        if (!str_contains($code, 'setup()') && !str_contains($code, 'setup ()')) {
            $errors[] = "Error: fungsi 'void setup()' tidak ditemukan.";
        }
        if (!str_contains($code, 'loop()') && !str_contains($code, 'loop ()')) {
            $errors[] = "Error: fungsi 'void loop()' tidak ditemukan.";
        }

        // Count open and close braces
        $openBraces = substr_count($code, '{');
        $closeBraces = substr_count($code, '}');
        if ($openBraces !== $closeBraces) {
            $errors[] = "Error Sintaks: Kurung kurawal '{' ($openBraces) dan '}' ($closeBraces) tidak seimbang.";
        }

        if (count($errors) > 0) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'errors' => implode("\n", $errors),
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'compiled',
            'message' => 'Kompilasi Berhasil (0 Warning, 0 Error).',
            'hex' => null, // Frontend AVR8js emulates execution
        ]);
    }

    /**
     * Delete a SimLab Project (supports JSON & redirect)
     */
    public function destroy($id)
    {
        $project = SimProject::find($id);

        if (!$project) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Proyek tidak ditemukan.'], 404);
            }
            return redirect()->route('simlab.index')->with('error', 'Proyek tidak ditemukan.');
        }

        if (!auth()->check() || $project->user_id === auth()->id() || (auth()->user() && auth()->user()->isAdmin())) {
            $project->delete();
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Proyek berhasil dihapus.']);
            }
            return redirect()->route('simlab.index')->with('success', 'Proyek berhasil dihapus.');
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus proyek ini.'], 403);
        }
        return redirect()->route('simlab.index')->with('error', 'Anda tidak memiliki izin untuk menghapus proyek ini.');
    }

    /**
     * Get My Projects List via JSON for Modal Manager
     */
    public function myProjects()
    {
        $user = auth()->user();
        if ($user) {
            $projects = SimProject::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $projects = SimProject::where('is_template', false)
                ->orderBy('updated_at', 'desc')
                ->take(20)
                ->get();
        }

        return response()->json([
            'success' => true,
            'projects' => $projects
        ]);
    }

    /**
     * Duplicate a SimLab Project
     */
    public function duplicate($id)
    {
        $original = SimProject::findOrFail($id);

        $newProject = $original->replicate();
        $newProject->title = $original->title . ' (Salinan)';
        $newProject->user_id = auth()->id();
        $newProject->share_token = \Illuminate\Support\Str::random(12);
        $newProject->created_at = now();
        $newProject->updated_at = now();
        $newProject->save();

        return redirect()->route('simlab.editor', $newProject->id)->with('success', 'Proyek berhasil diduplikasi.');
    }
}
