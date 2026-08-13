<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class MobileProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = null;
        $teacher = null;

        if ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->with('school')->first();
        } elseif (in_array($user->role, ['guru', 'pegawai'])) {
            $teacher = Teacher::where('user_id', $user->id)->with('school')->first();
        }

        return view('mobile.profile.index', compact('user', 'student', 'teacher'));
    }
}
