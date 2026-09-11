<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\LmsAssignment;
use App\Models\LmsClass;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsSubmission;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LmsAssignmentPdfValidationTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicYear $academicYear;
    protected Semester $semester;
    protected Subject $subject;
    protected Classroom $classroom;
    protected User $guruUser;
    protected Teacher $teacher;
    protected User $siswaUser;
    protected Student $student;
    protected LmsCourse $course;
    protected LmsAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->school = School::create([
            'name' => 'SMK Pembda Gunungsitoli',
            'type' => 'SMK',
            'npsn' => '20220002',
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::create([
            'year' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_number' => 1,
            'semester_name' => 'Ganjil 2025/2026',
            'start_date' => '2025-07-15',
            'end_date' => '2025-12-15',
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'subject_code' => 'KJR-01',
            'subject_name' => 'Dasar Kejuruan',
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'X-TE-1',
            'class_name' => 'X TE 1',
            'grade_level' => 10,
            'is_active' => true,
        ]);

        $this->guruUser = User::create([
            'name' => 'Guru Resman',
            'username' => 'guru_resman',
            'email' => 'resman@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $emp = Employee::create([
            'school_id' => $this->school->id,
            'employee_code' => 'EMP-RES-01',
            'full_name' => 'Resman H',
            'gender' => 'L',
            'employee_type' => 'guru',
            'employment_status' => 'yayasan',
            'tmt_date' => '2020-01-01',
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'employee_id' => $emp->id,
            'user_id' => $this->guruUser->id,
            'school_id' => $this->school->id,
            'teacher_code' => 'T-RES-01',
            'full_name' => 'Resman H',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $this->siswaUser = User::create([
            'name' => 'Siswa Pengumpul',
            'username' => 'siswa_pengumpul',
            'email' => 'siswa_pengumpul@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $this->student = Student::create([
            'user_id' => $this->siswaUser->id,
            'school_id' => $this->school->id,
            'nis' => '998877',
            'nisn' => '0012345678',
            'full_name' => 'Siswa Pengumpul',
            'gender' => 'L',
            'entry_year' => 2025,
            'status' => 'aktif',
        ]);

        $this->student->classrooms()->attach($this->classroom->id, [
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        $this->course = LmsCourse::create([
            'teacher_id' => $this->teacher->id,
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'course_name' => 'Praktikum Elektronika Dasar',
            'is_published' => true,
        ]);

        $lmsClass = LmsClass::create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'school_id' => $this->school->id,
            'status' => 'active',
        ]);

        LmsEnrollment::create([
            'lms_class_id' => $lmsClass->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $this->assignment = $this->course->assignments()->create([
            'title' => 'Laporan Praktikum 1',
            'description' => 'Silakan kumpulkan berkas laporan praktikum.',
            'assignment_type' => 'file',
            'is_group_assignment' => false,
            'max_score' => 100,
            'is_published' => true,
        ]);
    }

    public function test_student_can_submit_valid_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->siswaUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);
    }

    public function test_student_cannot_submit_bin_file(): void
    {
        $file = UploadedFile::fake()->create('tugas.bin', 100, 'application/octet-stream');

        $response = $this->actingAs($this->siswaUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'file' => $file,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_student_cannot_submit_txt_file(): void
    {
        $file = UploadedFile::fake()->create('catatan.txt', 50, 'text/plain');

        $response = $this->actingAs($this->siswaUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'file' => $file,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_student_cannot_submit_docx_file(): void
    {
        $file = UploadedFile::fake()->create('dokumen.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->siswaUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'file' => $file,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_mobile_student_cannot_submit_bin_file(): void
    {
        $file = UploadedFile::fake()->create('jawaban.bin', 100, 'application/octet-stream');

        $response = $this->actingAs($this->siswaUser)
            ->post(route('mobile.lms.assignment.submit', $this->assignment->id), [
                'file' => $file,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_mobile_student_can_submit_valid_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('jawaban_mobile.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->siswaUser)
            ->post(route('mobile.lms.assignment.submit', $this->assignment->id), [
                'file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);
    }
}
