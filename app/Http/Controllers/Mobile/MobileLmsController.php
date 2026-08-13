<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileLmsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $enrolledCourses = collect();
        if ($student) {
            $enrolledCourses = LmsCourse::whereHas('enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })->with('teacher')->get();
        } else {
            $enrolledCourses = LmsCourse::where('teacher_id', $user->teacher->id ?? 0)->get();
        }

        return view('mobile.lms.index', compact('enrolledCourses'));
    }

    public function catalog()
    {
        $courses = LmsCourse::where('is_published', true)
            ->with('teacher')
            ->latest()
            ->paginate(10);

        return view('mobile.lms.catalog', compact('courses'));
    }

    public function show($id)
    {
        $course = LmsCourse::with([
            'teacher',
            'modules.materials',
            'assignments',
            'quizzes'
        ])->findOrFail($id);

        return view('mobile.lms.show', compact('course'));
    }
}
