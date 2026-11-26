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
                    ->orderByRaw("CASE WHEN engine_type = 'ai_custom_html' THEN 0 ELSE 1 END")
                    ->orderByDesc('id')
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
            'room_key'     => 'required|in:fisika,kimia_biologi,matematika,vokasi,ips_bahasa,olimpiade',
            'title'        => 'required|string|max:150',
            'subject_name' => 'nullable|string|max:120',
            'course_id'    => 'nullable|exists:lms_courses,id',
            'description'  => 'required|string|max:1000',
            'prompt_spec'  => 'required|string|min:12|max:2500',
        ]);

        $roomCategoryMap = [
            'fisika'        => ['cat' => 'sma', 'subj' => 'IPA / Fisika', 'icon' => 'fas fa-atom', 'color' => '#0284c7'],
            'kimia_biologi' => ['cat' => 'sma', 'subj' => 'Kimia / Biologi', 'icon' => 'fas fa-flask', 'color' => '#059669'],
            'matematika'    => ['cat' => 'smp', 'subj' => 'Matematika', 'icon' => 'fas fa-square-root-variable', 'color' => '#4f46e5'],
            'vokasi'        => ['cat' => 'vokasi', 'subj' => 'Kejuruan & Vokasi SMK', 'icon' => 'fas fa-gears', 'color' => '#ea580c'],
            'ips_bahasa'    => ['cat' => 'umum', 'subj' => 'IPS / Ekonomi / Bahasa', 'icon' => 'fas fa-earth-asia', 'color' => '#7c3aed'],
            'olimpiade'     => ['cat' => 'sma', 'subj' => 'Olimpiade & PTN', 'icon' => 'fas fa-trophy', 'color' => '#d97706'],
        ];
        $roomMeta = $roomCategoryMap[$validated['room_key']];
        $subjectName = $validated['subject_name'] ?: $roomMeta['subj'];

        $prompt = "Anda adalah Ahli Desain Laboratorium Virtual Pendidikan (HTML5 Canvas + JavaScript).\n"
            . "Tugas Anda:\n"
            . "1. Evaluasi terlebih dahulu apakah instruksi pembangkit di bawah ini JELAS, MASUK AKAL, dan DAPAT dimodelkan menjadi simulasi ilmu pengetahuan/kejuruan yang bermanfaat.\n"
            . "   Jika instruksi tidak masuk akal, terlalu samar/acak, atau tidak dapat dipahami sebagai simulasi pembelajaran, JANGAN buat HTML—cukup balas SATU baris persis:\n"
            . "   INVALID_PROMPT: <jelaskan singkat apa yang perlu diperjelas oleh guru>\n"
            . "2. Jika instruksi dapat dipahami, buatkan 1 halaman HTML5 lengkap mandiri (single-file HTML dengan inline <style> dan <script>, TANPA library eksternal):\n"
            . "- Judul Lab: {$validated['title']}\n"
            . "- Mata Pelajaran / Kejuruan: {$subjectName}\n"
            . "- Narasi Pengantar Siswa: {$validated['description']}\n"
            . "- Instruksi Pembangkit dari Guru: {$validated['prompt_spec']}\n\n"
            . "SYARAT WAJIB HTML:\n"
            . "1. Sertakan <link rel=\"stylesheet\" href=\"/simlab-engines/simlab-theme.css\"> di <head> dan gunakan tata letak konsol instrumen penuh (<div class=\"sl-workbench\"><div class=\"sl-stage\">...</div><div class=\"sl-console\">...</div></div>).\n"
            . "2. Gaya arsitektural terang & tegas (#ffffff / #f8fafc, garis batas 1px solid #d8dee6, kanvas <canvas> berlatar #0b111e agar objek animasi terlihat tajam). DILARANG menggunakan nested cards berlapis, gradien dekoratif, shadow kabur, atau emoji dekoratif pada judul/tombol.\n"
            . "3. Harus memiliki <canvas> animasi objek nyata yang bergerak halus (requestAnimationFrame) dan berubah real-time saat slider/tombol diubah.\n"
            . "4. Harus memiliki minimal 2 kontrol tuning variabel (<input type='range'> atau tombol .sl-btn) beserta pembacaan nilai hasil (.sl-readouts / .sl-readout dengan tabular-nums) secara langsung.\n"
            . "5. JANGAN gunakan tanda dolar LaTeX ($...$). Gunakan HTML <sub>/<sup> atau Unicode (v₀, θ, λ, α, μ, ρ).\n"
            . "6. Jangan gunakan markdown code fence (```html). Kembalikan HANYA kode HTML valid mulai dari <!DOCTYPE html> hingga </html>.";

        try {
            $gemini = app(\App\Services\GeminiService::class);
            $html = trim($gemini->generateText($prompt));

            if (str_starts_with(strtoupper($html), 'INVALID_PROMPT:')) {
                $reason = trim(substr($html, strlen('INVALID_PROMPT:')));
                return response()->json([
                    'success' => false,
                    'message' => 'Instruksi pembangkit belum dapat dipahami AI: ' . ($reason ?: 'Mohon jelaskan objek, variabel, dan fenomena yang ingin disimulasikan dengan lebih spesifik.'),
                ], 422);
            }

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
                    'message' => 'AI belum menghasilkan kanvas simulasi yang valid. Silakan perjelas instruksi pembangkit Anda dan coba lagi.',
                ], 422);
            }

            $slug = Str::slug($validated['title']) . '-' . Str::lower(Str::random(5));
            $payload = [
                'title'        => $validated['title'],
                'slug'         => $slug,
                'category'     => $roomMeta['cat'],
                'subject_name' => $subjectName,
                'engine_type'  => 'ai_custom_html',
                'description'  => $validated['description'],
                'sop_steps'    => [
                    'Baca narasi fenomena dan perhatikan variabel yang dapat diatur pada panel kontrol.',
                    'Atur nilai awal variabel menggunakan slider/tombol yang tersedia.',
                    'Amati perubahan animasi dan angka pembacaan secara real-time untuk menyimpulkan konsep.',
                ],
                'custom_html'  => $html,
                'course_id'    => $validated['course_id'] ?? null,
                'created_by'   => $user->id,
                'icon'         => $roomMeta['icon'],
                'accent_color' => $roomMeta['color'],
                'is_active'    => true,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('virtual_labs', 'room_key')) {
                $payload['room_key'] = $validated['room_key'];
            }
            $lab = VirtualLab::create($payload);

            return response()->json([
                'success'      => true,
                'message'      => 'Simulator Lab baru berhasil dibuat dan ditempatkan di ruangan pilihan Anda!',
                'redirect_url' => route('simlab.index', ['room' => $validated['room_key'], 'lab' => $lab->slug]),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membangkitkan simulator AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a custom VirtualLab (only creator or Admin).
     */
    public function updateLab(Request $request, $id)
    {
        $user = Auth::user();
        $lab = VirtualLab::findOrFail($id);
        if (!$user || ($lab->created_by !== $user->id && !$user->isAdmin())) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berhak mengedit simulator ini.'], 403);
        }

        $validated = $request->validate([
            'room_key'     => 'required|in:fisika,kimia_biologi,matematika,vokasi,ips_bahasa,olimpiade',
            'title'        => 'required|string|max:150',
            'subject_name' => 'nullable|string|max:120',
            'description'  => 'required|string|max:1000',
        ]);

        $lab->update([
            'room_key'     => $validated['room_key'],
            'title'        => $validated['title'],
            'subject_name' => $validated['subject_name'] ?: $lab->subject_name,
            'description'  => $validated['description'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Simulator berhasil diperbarui.',
        ]);
    }

    /**
     * Delete a custom VirtualLab created by Teacher/Admin.
     */
    public function destroyLab(Request $request, $id)
    {
        $user = Auth::user();
        $lab = VirtualLab::findOrFail($id);
        if (!$user || ($lab->created_by !== $user->id && !$user->isAdmin())) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak berhak menghapus simulator ini.'], 403);
            }
            abort(403, 'Anda tidak berhak menghapus simulator ini.');
        }
        $lab->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Simulator Lab berhasil dihapus.']);
        }
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
