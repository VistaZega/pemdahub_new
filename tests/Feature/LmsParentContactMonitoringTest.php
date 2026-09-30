<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\Classroom;
use App\Models\ParentModel;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsParentContactMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicYear $academicYear;
    protected Semester $semester;
    protected Classroom $classroom;
    protected Teacher $teacher;
    protected User $teacherUser;

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
            'name' => 'Wali Kelas Teladan',
            'username' => 'walikelas',
            'email' => 'wali@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'school_id' => $this->school->id,
            'teacher_code' => 'GUR001',
            'nip' => '198501012010011001',
            'full_name' => 'Wali Kelas Teladan',
            'gender' => 'L',
            'phone' => '081234567890',
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'XII-TKJ-1',
            'class_name' => 'XII TKJ 1',
            'grade_level' => 12,
            'homeroom_teacher_id' => $this->teacher->id,
            'is_active' => true,
        ]);
    }

    public function test_student_model_resolves_parent_phone_from_parent_phone_column(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223344',
            'full_name' => 'Ahmad Farid Mendrofa',
            'gender' => 'L',
            'phone' => '082246122535',
            'parent_name' => 'Abdul Haris mendrofa',
            'parent_phone' => '085261509533',
            'entry_year' => 2024,
            'status' => 'aktif',
        ]);

        $this->assertEquals('Abdul Haris mendrofa', $student->effective_parent_name);
        $this->assertEquals('085261509533', $student->effective_parent_phone);
        $this->assertEquals('6285261509533', $student->formatted_parent_wa_phone);
        $this->assertEquals('6282246122535', $student->formatted_student_wa_phone);
    }

    public function test_student_model_falls_back_to_guardian_phone_and_name(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223345',
            'full_name' => 'Siswa Wali Only',
            'gender' => 'L',
            'phone' => '081111111111',
            'guardian_name' => 'Bapak Wali Murid',
            'guardian_phone' => '081298765432',
            'entry_year' => 2024,
            'status' => 'aktif',
        ]);

        $this->assertEquals('Bapak Wali Murid', $student->effective_parent_name);
        $this->assertEquals('081298765432', $student->effective_parent_phone);
        $this->assertEquals('6281298765432', $student->formatted_parent_wa_phone);
    }

    public function test_student_model_falls_back_to_parents_relation_table(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223346',
            'full_name' => 'Siswa Parent Relation',
            'gender' => 'P',
            'phone' => '081111111112',
            'entry_year' => 2024,
            'status' => 'aktif',
        ]);

        $parentUser = User::create([
            'name' => 'Ibu Kandung',
            'username' => 'ibukandung',
            'email' => 'ibu@pembda.test',
            'password' => bcrypt('password'),
            'role' => 'orang_tua',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        ParentModel::create([
            'user_id' => $parentUser->id,
            'student_id' => $student->id,
            'relation_type' => 'ibu',
            'full_name' => 'Ibu Kandung Tersayang',
            'phone' => '081377778888',
        ]);

        $student->load('parents');

        $this->assertEquals('Ibu Kandung Tersayang', $student->effective_parent_name);
        $this->assertEquals('081377778888', $student->effective_parent_phone);
        $this->assertEquals('6281377778888', $student->formatted_parent_wa_phone);
    }

    public function test_student_model_falls_back_to_applicant_record(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223347',
            'full_name' => 'Siswa Dari PSB',
            'gender' => 'L',
            'phone' => '081111111113',
            'entry_year' => 2024,
            'status' => 'aktif',
        ]);

        Applicant::create([
            'student_id' => $student->id,
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'registration_number' => 'REG-2026-001',
            'nisn' => '0011223347',
            'full_name' => 'Siswa Dari PSB',
            'gender' => 'L',
            'father_name' => 'Ayah dari PSB',
            'father_phone' => '085299990000',
        ]);

        $student->load('applicant');

        $this->assertEquals('Ayah dari PSB', $student->effective_parent_name);
        $this->assertEquals('085299990000', $student->effective_parent_phone);
        $this->assertEquals('6285299990000', $student->formatted_parent_wa_phone);
    }

    public function test_homeroom_student_detail_api_returns_effective_parent_contact(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223348',
            'full_name' => 'Ahmad Farid Mendrofa',
            'gender' => 'L',
            'phone' => '082246122535',
            'parent_name' => 'Abdul Haris mendrofa',
            'parent_phone' => '085261509533',
            'entry_year' => 2024,
            'status' => 'aktif',
        ]);

        StudentClass::create([
            'student_id' => $student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->getJson(route('guru.walikelas.lms-monitoring.student-detail', $student->id));

        $response->assertOk();
        $response->assertJsonPath('student.name', 'Ahmad Farid Mendrofa');
        $response->assertJsonPath('student.parent_name', 'Abdul Haris mendrofa');
        $response->assertJsonPath('student.parent_phone', '6285261509533');
        $response->assertJsonPath('student.student_phone', '6282246122535');
    }

    public function test_homeroom_send_motivation_auto_syncs_parent_phone_to_student(): void
    {
        $student = Student::create([
            'school_id' => $this->school->id,
            'nisn' => '0011223349',
            'full_name' => 'Siswa Tanpa Nomor Awal',
            'gender' => 'L',
            'entry_year' => 2024,
            'status' => 'aktif',
        ]);

        StudentClass::create([
            'student_id' => $student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('guru.walikelas.lms-monitoring.motivation'), [
                'student_id' => $student->id,
                'message_type' => 'motivasi',
                'target_recipient' => 'parent',
                'note' => 'Ayo semangat belajar di LMS ya ananda!',
                'target_phone' => '081234567899',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertStringContainsString('6281234567899', $response->json('wa_url'));

        // Pastikan nomor otomatis tersimpan di data profil siswa
        $student->refresh();
        $this->assertEquals('081234567899', $student->parent_phone);
        $this->assertEquals('081234567899', $student->guardian_phone);
    }
}
