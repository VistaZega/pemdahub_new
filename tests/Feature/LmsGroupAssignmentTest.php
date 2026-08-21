<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentGroup;
use App\Models\LmsClass;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsSubmission;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\GradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsGroupAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicYear $academicYear;
    protected Semester $semester;
    protected Subject $subject;
    protected Classroom $classroom;

    protected User $guruUser;
    protected Teacher $teacher;

    protected User $leaderUser;
    protected Student $leaderStudent;

    protected User $memberUser;
    protected Student $memberStudent;

    protected User $otherStudentUser;
    protected Student $otherStudent;

    protected LmsCourse $course;
    protected LmsAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

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
            'subject_code' => 'KOD-01',
            'subject_name' => 'Pemrograman Mikro',
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'X-RPL',
            'class_name' => 'X RPL 1',
            'grade_level' => 10,
            'is_active' => true,
        ]);

        // Guru
        $this->guruUser = User::create([
            'name' => 'Bapak Guru Pengampu',
            'username' => 'guru_pengampu',
            'email' => 'guru@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'school_id' => $this->school->id,
            'employee_code' => 'EMP-GRP-001',
            'full_name' => 'Bapak Guru Pengampu',
            'gender' => 'L',
            'employee_type' => 'guru',
            'employment_status' => 'yayasan',
            'tmt_date' => '2020-01-01',
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'employee_id' => $employee->id,
            'user_id' => $this->guruUser->id,
            'school_id' => $this->school->id,
            'teacher_code' => 'GR-GRP-001',
            'full_name' => 'Bapak Guru Pengampu',
            'gender' => 'L',
            'education_level' => 'S1',
            'major' => 'Teknik Komputer',
            'religion' => 'Kristen',
            'is_active' => true,
        ]);

        // Siswa 1 (Ketua)
        $this->leaderUser = User::create([
            'name' => 'Siswa Ketua Kelompok',
            'username' => 'siswa_ketua',
            'email' => 'ketua@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);
        $this->leaderStudent = Student::create([
            'user_id' => $this->leaderUser->id,
            'school_id' => $this->school->id,
            'nisn' => '0011223344',
            'nis' => '112233',
            'full_name' => 'Siswa Ketua Kelompok',
            'gender' => 'L',
            'entry_year' => 2025,
            'status' => 'aktif',
        ]);
        StudentClass::create([
            'student_id' => $this->leaderStudent->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        // Siswa 2 (Anggota)
        $this->memberUser = User::create([
            'name' => 'Siswa Anggota Kelompok',
            'username' => 'siswa_anggota',
            'email' => 'anggota@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);
        $this->memberStudent = Student::create([
            'user_id' => $this->memberUser->id,
            'school_id' => $this->school->id,
            'nisn' => '0011223355',
            'nis' => '112234',
            'full_name' => 'Siswa Anggota Kelompok',
            'gender' => 'P',
            'entry_year' => 2025,
            'status' => 'aktif',
        ]);
        StudentClass::create([
            'student_id' => $this->memberStudent->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        // Siswa 3 (Siswa Lain / Belum Masuk Kelompok)
        $this->otherStudentUser = User::create([
            'name' => 'Siswa Tanpa Kelompok',
            'username' => 'siswa_lain',
            'email' => 'other@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);
        $this->otherStudent = Student::create([
            'user_id' => $this->otherStudentUser->id,
            'school_id' => $this->school->id,
            'nisn' => '0011223366',
            'nis' => '112235',
            'full_name' => 'Siswa Tanpa Kelompok',
            'gender' => 'L',
            'entry_year' => 2025,
            'status' => 'aktif',
        ]);
        StudentClass::create([
            'student_id' => $this->otherStudent->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        // Course & LMS Class
        $this->course = LmsCourse::create([
            'teacher_id' => $this->teacher->id,
            'school_id' => $this->school->id,
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'course_name' => 'Bahasa Pemrograman Mikro',
            'is_published' => true,
        ]);

        $lmsClass = LmsClass::create([
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'school_id' => $this->school->id,
            'status' => 'active',
        ]);

        LmsEnrollment::create(['lms_class_id' => $lmsClass->id, 'student_id' => $this->leaderStudent->id, 'status' => 'enrolled']);
        LmsEnrollment::create(['lms_class_id' => $lmsClass->id, 'student_id' => $this->memberStudent->id, 'status' => 'enrolled']);
        LmsEnrollment::create(['lms_class_id' => $lmsClass->id, 'student_id' => $this->otherStudent->id, 'status' => 'enrolled']);

        // Group Assignment
        $this->assignment = $this->course->assignments()->create([
            'title' => 'Tugas Kelompok 1 - Rangkaian',
            'description' => 'Kerjakan secara berkelompok.',
            'assignment_type' => 'text',
            'is_group_assignment' => true,
            'max_score' => 100,
            'is_published' => true,
            'allow_resubmit' => true,
        ]);
    }

    /**
     * 1. Test Guru Membuat Kelompok dan Mengaitkan Siswa
     */
    public function test_guru_can_create_group_and_assign_leader_and_members()
    {
        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.lms.assignments.groups.store', $this->assignment->id), [
                'name' => 'Kelompok Alpha',
                'leader_id' => $this->leaderStudent->id,
                'member_ids' => [$this->leaderStudent->id, $this->memberStudent->id],
            ]);

        $response->assertRedirect(route('guru.lms.assignments.show', $this->assignment->id));

        $this->assertDatabaseHas('lms_assignment_groups', [
            'assignment_id' => $this->assignment->id,
            'name' => 'Kelompok Alpha',
            'leader_id' => $this->leaderStudent->id,
        ]);

        $group = LmsAssignmentGroup::where('name', 'Kelompok Alpha')->first();
        $this->assertTrue($group->hasMember($this->leaderStudent->id));
        $this->assertTrue($group->hasMember($this->memberStudent->id));
        $this->assertTrue($group->isLeader($this->leaderStudent->id));
        $this->assertFalse($group->isLeader($this->memberStudent->id));
    }

    /**
     * 2. Test Anggota biasa ditolak saat mencoba mengumpulkan tugas kelompok
     */
    public function test_non_leader_member_cannot_submit_group_assignment()
    {
        $group = $this->assignment->groups()->create([
            'name' => 'Kelompok Alpha',
            'leader_id' => $this->leaderStudent->id,
        ]);
        $group->members()->sync([$this->leaderStudent->id, $this->memberStudent->id]);

        // Siswa Anggota (bukan ketua) mencoba submit
        $response = $this->actingAs($this->memberUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'submission_text' => 'Ini jawaban dari anggota biasa.',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('lms_submissions', [
            'assignment_id' => $this->assignment->id,
        ]);
    }

    /**
     * 3. Test Siswa tanpa kelompok ditolak saat mencoba mengumpulkan tugas kelompok
     */
    public function test_student_without_group_cannot_submit_group_assignment()
    {
        // Siswa tanpa kelompok mencoba submit
        $response = $this->actingAs($this->otherStudentUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'submission_text' => 'Saya belum punya kelompok.',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('lms_submissions', [
            'assignment_id' => $this->assignment->id,
        ]);
    }

    /**
     * 4. Test Ketua Kelompok BERHASIL mengumpulkan tugas atas nama kelompok
     */
    public function test_leader_can_successfully_submit_group_assignment()
    {
        $group = $this->assignment->groups()->create([
            'name' => 'Kelompok Alpha',
            'leader_id' => $this->leaderStudent->id,
        ]);
        $group->members()->sync([$this->leaderStudent->id, $this->memberStudent->id]);

        // Ketua submit
        $response = $this->actingAs($this->leaderUser)
            ->post(route('siswa.lms.assignments.submit', $this->assignment->id), [
                'submission_text' => 'Jawaban lengkap dari Kelompok Alpha.',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lms_submissions', [
            'assignment_id' => $this->assignment->id,
            'group_id' => $group->id,
            'student_id' => $this->leaderStudent->id,
            'submission_text' => 'Jawaban lengkap dari Kelompok Alpha.',
            'status' => 'submitted',
        ]);
    }

    /**
     * 5. Test Halaman Siswa: Anggota dapat melihat tugas kelompok yang sudah dikumpulkan ketua
     */
    public function test_member_can_view_group_details_and_submission_status()
    {
        $group = $this->assignment->groups()->create([
            'name' => 'Kelompok Alpha',
            'leader_id' => $this->leaderStudent->id,
        ]);
        $group->members()->sync([$this->leaderStudent->id, $this->memberStudent->id]);

        // Ketua submit tugas
        LmsSubmission::create([
            'assignment_id' => $this->assignment->id,
            'group_id' => $group->id,
            'student_id' => $this->leaderStudent->id,
            'submission_text' => 'Hasil diskusi kelompok kami.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Anggota membuka halaman LMS
        $response = $this->actingAs($this->memberUser)
            ->get(route('siswa.lms.show', $this->course->id));

        $response->assertStatus(200);
        $response->assertSee('Kelompok Alpha');
        $response->assertSee('Siswa Ketua Kelompok');
        $response->assertSee('Hasil diskusi kelompok kami.');
        $response->assertSee('sudah dikumpulkan');
    }

    /**
     * 6. Test Penilaian oleh Guru otomatis terdistribusi & tersinkronisasi ke SEMUA anggota kelompok
     */
    public function test_teacher_grading_group_submission_syncs_to_all_group_members()
    {
        $group = $this->assignment->groups()->create([
            'name' => 'Kelompok Alpha',
            'leader_id' => $this->leaderStudent->id,
        ]);
        $group->members()->sync([$this->leaderStudent->id, $this->memberStudent->id]);

        $submission = LmsSubmission::create([
            'assignment_id' => $this->assignment->id,
            'group_id' => $group->id,
            'student_id' => $this->leaderStudent->id,
            'submission_text' => 'Hasil diskusi kelompok.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Guru memberi nilai 95 dan feedback
        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.lms.submissions.grade', $submission->id), [
                'score' => 95,
                'feedback' => 'Kerja kelompok sangat memuaskan!',
                'action_type' => 'grade',
            ]);

        $response->assertSessionHas('success');

        // Submisi kelompok terupdate
        $submission->refresh();
        $this->assertEquals(95, $submission->score);
        $this->assertEquals('graded', $submission->status);
        $this->assertEquals('Kerja kelompok sangat memuaskan!', $submission->feedback);

        // Cek bahwa nilai disinkronkan ke rekap tabel `grades` untuk Ketua DAN Anggota
        $leaderGrade = Grade::where('student_id', $this->leaderStudent->id)
            ->where('subject_id', $this->subject->id)
            ->where('score', 95)
            ->first();
        $this->assertNotNull($leaderGrade, 'Nilai Ketua harus tercatat di tabel Grade');

        $memberGrade = Grade::where('student_id', $this->memberStudent->id)
            ->where('subject_id', $this->subject->id)
            ->where('score', 95)
            ->first();
        $this->assertNotNull($memberGrade, 'Nilai Anggota harus otomatis tercatat di tabel Grade');

        // Siswa tanpa kelompok TIDAK boleh dapat nilai
        $otherGrade = Grade::where('student_id', $this->otherStudent->id)
            ->where('subject_id', $this->subject->id)
            ->first();
        $this->assertNull($otherGrade, 'Siswa tanpa kelompok tidak boleh menerima nilai kelompok ini');
    }

    /**
     * 7. Test Guru Membagi Kelompok Secara Otomatis
     */
    public function test_guru_can_auto_generate_groups()
    {
        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.lms.assignments.groups.autoGenerate', $this->assignment->id), [
                'group_count' => 2,
            ]);

        $response->assertRedirect(route('guru.lms.assignments.show', $this->assignment->id));
        $this->assertDatabaseCount('lms_assignment_groups', 2);
    }

    /**
     * 8. Test Siswa yang sudah masuk kelompok tidak bisa ditambahkan ke kelompok lain
     */
    public function test_guru_cannot_add_student_who_is_already_in_another_group()
    {
        // Kelompok 1 sudah dibuat dengan leaderStudent dan memberStudent
        $group1 = $this->assignment->groups()->create([
            'name' => 'Kelompok 1',
            'leader_id' => $this->leaderStudent->id,
        ]);
        $group1->members()->sync([$this->leaderStudent->id, $this->memberStudent->id]);

        // Coba buat Kelompok 2 dengan leaderStudent yang sama (harus ditolak)
        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.lms.assignments.groups.store', $this->assignment->id), [
                'name' => 'Kelompok 2',
                'leader_id' => $this->leaderStudent->id,
                'member_ids' => [$this->otherStudent->id],
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('lms_assignment_groups', 1);

        // Coba buat Kelompok 2 dengan otherStudent sebagai ketua tapi memberStudent (yang sudah di Kelompok 1) sebagai anggota
        $response2 = $this->actingAs($this->guruUser)
            ->post(route('guru.lms.assignments.groups.store', $this->assignment->id), [
                'name' => 'Kelompok 2',
                'leader_id' => $this->otherStudent->id,
                'member_ids' => [$this->memberStudent->id],
            ]);

        $response2->assertSessionHas('error');
        $this->assertDatabaseCount('lms_assignment_groups', 1);
    }
}
