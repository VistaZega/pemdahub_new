<?php

namespace App\Http\Controllers;

use App\Models\AlumniProfile;
use App\Models\TracerStudy;
use App\Models\JobPosting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumniController extends Controller
{
    private function getAlumni(): AlumniProfile
    {
        $student = Student::where('user_id', Auth::id())->first();
        if ($student && !in_array($student->status, ['lulus', 'alumni'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk alumni yang telah lulus.');
        }

        if (!$student) {
            // Cek apakah user mendaftar melalui IKA Alumni (AlumniDirectory)
            $alumniDir = \App\Models\AlumniDirectory::where('user_id', Auth::id())->first();
            if ($alumniDir) {
                return AlumniProfile::firstOrCreate(
                    ['email' => Auth::user()->email],
                    [
                        'school_id' => $alumniDir->school_id,
                        'full_name' => $alumniDir->full_name,
                        'graduation_year' => $alumniDir->graduation_year,
                        'phone' => $alumniDir->phone,
                    ]
                );
            }

            // Check if user is already mapped as alumni directly
            $alumni = AlumniProfile::where('email', Auth::user()->email)->first();
            if ($alumni) return $alumni;

            // Allow Admin / Staff roles to preview and interact as well
            if (in_array(Auth::user()->role, ['superadmin', 'admin_yayasan', 'admin_sekolah', 'guru'])) {
                return AlumniProfile::firstOrCreate(
                    ['email' => Auth::user()->email],
                    [
                        'school_id' => Auth::user()->school_id ?? 1,
                        'full_name' => Auth::user()->name,
                        'graduation_year' => now()->format('Y'),
                        'phone' => '-',
                    ]
                );
            }
            
            abort(403, 'Akses dibatasi hanya untuk alumni.');
        }

        return AlumniProfile::firstOrCreate(
            ['student_id' => $student->id],
            [
                'school_id' => $student->school_id,
                'full_name' => $student->full_name,
                'graduation_year' => $student->entry_year ? ($student->entry_year + 3) : now()->format('Y'),
                'phone' => $student->phone,
                'email' => Auth::user()->email,
            ]
        );
    }

    public function tracerForm()
    {
        $alumni = $this->getAlumni();
        
        $tracer = TracerStudy::where('alumni_profile_id', $alumni->id)->first();

        return view('alumni.tracer_form', compact('alumni', 'tracer'));
    }

    public function tracerSubmit(Request $request)
    {
        $alumni = $this->getAlumni();

        $validated = $request->validate([
            'employment_status' => 'required|in:kerja,kuliah,wirausaha,mencari_kerja,lainnya',
            'company_name' => 'nullable|required_if:employment_status,kerja|string|max:255',
            'job_title' => 'nullable|required_if:employment_status,kerja|string|max:255',
            'salary_range' => 'nullable|string',
            'university_name' => 'nullable|required_if:employment_status,kuliah|string|max:255',
            'major' => 'nullable|required_if:employment_status,kuliah|string|max:255',
            'wirausaha_field' => 'nullable|required_if:employment_status,wirausaha|string|max:255',
            'feedback_for_school' => 'nullable|string|max:1000',
        ]);

        $tracer = TracerStudy::updateOrCreate(
            ['alumni_profile_id' => $alumni->id],
            [
                'employment_status' => $validated['employment_status'],
                'company_name' => $validated['company_name'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'salary_range' => $validated['salary_range'] ?? null,
                'university_name' => $validated['university_name'] ?? null,
                'major' => $validated['major'] ?? null,
                'wirausaha_field' => $validated['wirausaha_field'] ?? null,
                'feedback_for_school' => $validated['feedback_for_school'] ?? null,
                'survey_date' => now(),
            ]
        );

        if ($alumni->student && $alumni->student->user_id) {
            \App\Models\ReputationLog::log(
                $alumni->student->user_id,
                50,
                'alumni_tracer',
                'Mengisi survei Tracer Study Alumni',
                $tracer
            );
        }

        return redirect()->route('alumni.tracer.form')->with('success', 'Data Tracer Study berhasil diperbarui.');
    }

    public function kontribusi()
    {
        $alumni = $this->getAlumni();
        return view('alumni.kontribusi', compact('alumni'));
    }

    public function jobsIndex()
    {
        $jobs = JobPosting::where('is_active', true)->latest()->get();
        return view('alumni.jobs', compact('jobs'));
    }
}
