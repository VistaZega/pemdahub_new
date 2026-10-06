<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LmsAssignment;
use App\Models\LmsClass;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsMaterial;
use App\Models\LmsMaterialProgress;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsSubmission;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherLmsMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicYear $academicYear;
    protected Semester $semester;
    protected Classroom $classroom;
    protected Teacher $teacher;
    protected User $teacherUser;
    protected Subject $subject;
    protected LmsCourse $course;
    protected LmsClass $lmsClass;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMK Swasta Pembda Nias',
            'npsn' => '12345678',
            'type' => 'smk',
            'level' => 'SMK',
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::create([
            'year' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_number' => 1,
            'semester_name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $this->teacherUser = User::create([
            'name' => 'Adiyusu Zai, S.Pd',
            'username' => 'adiszai',
            'email' => 'adis@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'school_id' => $this->school->id,
            'teacher_code' => 'ADIS01',
            'nip' => '198501012010011002',
            'full_name' => 'Adiyusu Zai, S.Pd',
            'gender' => 'L',
            'phone' => '081234567890',
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'XI-TKJ',
            'class_name' => 'XI TKJ',
            'grade_level' => 11,
            'homeroom_teacher_id' => $this->teacher->id,
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'name' => 'Matematika',
            'code' => 'MTK-11',
            'is_active' => true,
        ]);

        $this->course = LmsCourse::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'course_name' => 'Matriks Kelas 11',
            'course_code' => 'MTK-XI-01',
            'is_published' => true,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->lmsClass = LmsClass::create([
            'school_id' => $this->school->id,
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
        ]);

        $this->student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223399',
            'full_name' => 'Siswa Belajar Matriks',
            'gender' => 'L',
            'phone' => '082211223344',
            'parent_name' => 'Orang Tua Siswa',
            'parent_phone' => '085211223344',
            'entry_year' => 2025,
            'status' => 'aktif',
        ]);

        StudentClass::create([
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        LmsEnrollment::create([
            'lms_class_id' => $this->lmsClass->id,
            'student_id' => $this->student->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);
    }

    public function test_teacher_monitoring_index_renders_without_undefined_variable_error(): void
    {
        // Add material, assignment, and quiz
        $mat = LmsMaterial::create([
            'course_id' => $this->course->id,
            'title' => 'Konsep Matriks',
            'material_type' => 'document',
            'is_published' => true,
        ]);

        $assign = LmsAssignment::create([
            'course_id' => $this->course->id,
            'title' => 'Tugas 1 Operasi Matriks',
            'is_published' => true,
        ]);

        $quiz = LmsQuiz::create([
            'course_id' => $this->course->id,
            'title' => 'Kuis Matriks',
            'is_published' => true,
        ]);

        // Student completes material
        LmsMaterialProgress::create([
            'material_id' => $mat->id,
            'student_id' => $this->student->id,
            'status' => 'completed',
        ]);

        // Student submits assignment
        LmsSubmission::create([
            'assignment_id' => $assign->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Student attempts quiz
        LmsQuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $this->student->id,
            'started_at' => now()->subMinutes(30),
            'finished_at' => now(),
            'score' => 95,
        ]);

        // Access monitoring page
        $response = $this->actingAs($this->teacherUser)
            ->get(route('guru.lms.monitoring.index'));

        $response->assertOk();
        $response->assertSee('Matriks Kelas 11');
        $response->assertSee('Siswa Belajar Matriks');
        $response->assertSee('XI TKJ');
    }

    public function test_teacher_monitoring_index_with_rombel_filter(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('guru.lms.monitoring.index', [
                'course_id' => $this->course->id,
                'rombel' => 'XI TKJ',
            ]));

        $response->assertOk();
        $response->assertSee('Siswa Belajar Matriks');
    }

    public function test_teacher_monitoring_export_excel_stream(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('guru.lms.monitoring.export', [
                'course_id' => $this->course->id,
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_teacher_monitoring_action_praise_or_warning_syncs_phone(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('guru.lms.monitoring.action'), [
                'student_id' => $this->student->id,
                'course_id' => $this->course->id,
                'type' => 'apresiasi',
                'message' => 'Luar biasa, pertahankan nilaimu!',
                'target_phone' => '081299887766',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertStringContainsString('6281299887766', $response->json('wa_url'));

        $this->student->refresh();
        $this->assertEquals('081299887766', $this->student->parent_phone);
    }
}
