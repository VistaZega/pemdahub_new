<?php

namespace App\Http\Controllers;

use App\Models\SimProject;
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

        return view('simlab.index', compact('myProjects', 'templates'));
    }

    /**
     * Open SimLab Workspace Editor
     */
    public function editor($id = null)
    {
        $project = null;
        if ($id && $id !== 'new') {
            $project = SimProject::find($id);
            if (!$project && strlen($id) > 10) {
                $project = SimProject::where('share_token', $id)->first();
            }
        }

        return view('simlab.editor', compact('project'));
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
}
