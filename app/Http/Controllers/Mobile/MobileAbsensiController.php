<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileAbsensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeRole = session('active_role', $user->role);

        if ($activeRole === 'guru' || $user->isGuru()) {
            return redirect()->route('mobile.guru.absensi.input');
        }

        $student = Student::where('user_id', $user->id)->first();
        $attendances = collect();

        if ($student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->orderBy('date', 'desc')
                ->take(30)
                ->get();
        }

        $todayAttendance = null;
        if ($student) {
            $todayAttendance = Attendance::where('student_id', $student->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();
        }

        return view('mobile.absensi.index', compact('student', 'attendances', 'todayAttendance'));
    }

    public function scan(Request $request)
    {
        // Re-route to existing GPS scan handler
        $apiController = app(\App\Http\Controllers\Api\AttendanceController::class);
        return $apiController->handleGpsScan($request);
    }
}
