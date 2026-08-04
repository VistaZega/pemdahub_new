<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherCompetencyController extends Controller
{
    /**
     * Display competencies management page for a teacher
     */
    public function index(Teacher $teacher, Request $request)
    {
        $teacher->load(['competentSubjects']);
        
        // Get all subjects for this school (teacher's school)
        if ($teacher->school_id == 4) {
            $allSubjects = Subject::where('is_active', 1)
                ->orderBy('school_id')
                ->orderBy('subject_name')
                ->get();
        } else {
            $allSubjects = Subject::where('school_id', $teacher->school_id)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();
        }
        
        // Get assigned subject IDs
        $assignedSubjectIds = $teacher->competentSubjects->pluck('id')->toArray();
        $returnUrl = $request->query('return_url');
        
        return view('admin.teachers.competencies', compact('teacher', 'allSubjects', 'assignedSubjectIds', 'returnUrl'));
    }
    
    /**
     * Assign subjects to teacher (bulk update)
     */
    public function update(Request $request, Teacher $teacher)
    {
        
        $validated = $request->validate([
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);
        
        DB::beginTransaction();
        try {
            // Detach all current subjects
            $teacher->competentSubjects()->detach();
            
            // Attach new subjects
            if (!empty($validated['subjects'])) {
                $syncData = [];
                foreach ($validated['subjects'] as $subjectId) {
                    $syncData[$subjectId] = [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $teacher->competentSubjects()->attach($syncData);
            }
            
            DB::commit();
            
            if ($request->filled('return_url')) {
                return redirect($request->return_url)
                    ->with('success', 'Kompetensi guru berhasil diperbarui');
            }
            
            return redirect()
                ->route('admin.teachers.competencies', $teacher->id)
                ->with('success', 'Kompetensi guru berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui kompetensi: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui kompetensi. Silakan coba lagi.']);
        }
    }
    
    /**
     * Quick add single subject competency (AJAX)
     */
    public function store(Request $request, Teacher $teacher)
    {
        
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);
        
        // Check if already assigned
        if ($teacher->competentSubjects()->where('subject_id', $validated['subject_id'])->exists()) {
            return response()->json(['message' => 'Mata pelajaran sudah ditambahkan'], 422);
        }
        
        $teacher->competentSubjects()->attach($validated['subject_id'], [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Kompetensi berhasil ditambahkan',
            'subject' => Subject::find($validated['subject_id']),
        ]);
    }
    
    /**
     * Remove subject competency (AJAX)
     */
    public function destroy(Teacher $teacher, Subject $subject)
    {
        
        $teacher->competentSubjects()->detach($subject->id);
        
        return response()->json(['message' => 'Kompetensi berhasil dihapus']);
    }
    
    /**
     * Get competent teachers for a subject (AJAX endpoint for schedule grid)
     */
    public function getTeachersBySubject(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);
        
        $subject = Subject::where('id', $validated['subject_id'])
            ->where('school_id', $user->school_id)
            ->firstOrFail();
        
        $teachers = $subject->competentTeachers()
            ->select('teachers.id', 'teachers.full_name')
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->full_name,
                ];
            });
        
        return response()->json([
            'teachers' => $teachers,
            'count' => $teachers->count(),
        ]);
    }
}

