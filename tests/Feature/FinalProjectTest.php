<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\FinalProject;
use App\Models\FinalProjectFormat;
use App\Models\FinalProjectLog;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalProjectTest extends TestCase
{
    use RefreshDatabase;

    protected $academicYear;
    protected $sma;
    protected $smk;
    protected $smaStudent;
    protected $smaStudentUser;
    protected $smkStudent;
    protected $smkStudentUser;
    protected $smaTeacher;
    protected $smaTeacherUser;
    protected $smkTeacher;
    protected $smkTeacherUser;
    protected $smaAdminUser;
    protected $smkAdminUser;

    public function setUp(): void
    {
        parent::setUp();
        // Seed base data
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        Storage::fake('public');

        $this->academicYear = AcademicYear::where('is_active', true)->first();
        $this->sma = School::where('type', 'SMA')->first();
        $this->smk = School::where('type', 'SMK')->first();

        // 1. Create Class XII Classrooms
        $smaClass = Classroom::create([
            'school_id' => $this->sma->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'XII-IPA-1',
            'class_name' => 'XII IPA 1',
            'class_type' => 'reguler',
            'grade_level' => 12,
            'is_active' => true,
        ]);

        $smkClass = Classroom::create([
            'school_id' => $this->smk->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'XII-TKJ-1',
            'class_name' => 'XII TKJ 1',
            'class_type' => 'reguler',
            'grade_level' => 12,
            'is_active' => true,
        ]);

        // 2. Fetch/create students
        $this->smaStudent = Student::where('school_id', $this->sma->id)->first();
        $this->smaStudentUser = $this->smaStudent->user;
        $this->smaStudentUser->update(['must_change_password' => false]);
        StudentClass::where('student_id', $this->smaStudent->id)->delete();
        StudentClass::create([
            'student_id' => $this->smaStudent->id,
            'classroom_id' => $smaClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        $this->smkStudent = Student::where('school_id', $this->smk->id)->first();
        $this->smkStudentUser = $this->smkStudent->user;
        $this->smkStudentUser->update(['must_change_password' => false]);
        StudentClass::where('student_id', $this->smkStudent->id)->delete();
        StudentClass::create([
            'student_id' => $this->smkStudent->id,
            'classroom_id' => $smkClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        // 3. Teachers
        $this->smaTeacher = Teacher::where('school_id', $this->sma->id)->first();
        $this->smaTeacherUser = $this->smaTeacher->user;
        $this->smaTeacherUser->update(['must_change_password' => false]);

        $this->smkTeacher = Teacher::where('school_id', $this->smk->id)->first();
        $this->smkTeacherUser = $this->smkTeacher->user;
        $this->smkTeacherUser->update(['must_change_password' => false]);

        // 4. Admin Users
        $this->smaAdminUser = User::where('role', 'admin_sekolah')->where('school_id', $this->sma->id)->first();
        $this->smaAdminUser->update(['must_change_password' => false]);

        $this->smkAdminUser = User::where('role', 'admin_sekolah')->where('school_id', $this->smk->id)->first();
        $this->smkAdminUser->update(['must_change_password' => false]);
    }

    public function test_non_grade_12_students_cannot_access_final_project_portal()
    {
        // Place student in Class X
        $classX = Classroom::create([
            'school_id' => $this->sma->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'X-1',
            'class_name' => 'X 1',
            'class_type' => 'reguler',
            'grade_level' => 10,
            'is_active' => true,
        ]);

        // Re-assign student class
        StudentClass::where('student_id', $this->smaStudent->id)->delete();
        StudentClass::create([
            'student_id' => $this->smaStudent->id,
            'classroom_id' => $classX->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->smaStudentUser)
            ->get(route('siswa.final-project.index'));

        $response->assertStatus(403);
    }

    public function test_grade_12_students_can_view_propose_page_and_submit_proposal()
    {
        $response = $this->actingAs($this->smaStudentUser)
            ->get(route('siswa.final-project.index'));

        $response->assertStatus(200);
        $response->assertViewIs('siswa.final_projects.propose');

        // Submit proposal
        $proposalData = [
            'title' => 'Pengaruh Kadar Gula Terhadap Fermentasi Nira Kelapa',
            'abstract' => 'Penelitian ini bertujuan untuk meneliti proses kimia fermentasi air nira kelapa.',
        ];

        $submitResponse = $this->actingAs($this->smaStudentUser)
            ->post(route('siswa.final-project.propose'), $proposalData);

        $submitResponse->assertRedirect(route('siswa.final-project.index'));
        $this->assertDatabaseHas('final_projects', [
            'student_id' => $this->smaStudent->id,
            'title' => $proposalData['title'],
            'type' => 'penelitian_ilmiah',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_upload_and_delete_formats()
    {
        $file = UploadedFile::fake()->create('panduan.pdf', 500);

        $response = $this->actingAs($this->smaAdminUser)
            ->post(route('admin.final-projects.formats.store'), [
                'title' => 'Panduan Penelitian SMA',
                'description' => 'Berkas acuan penyusunan karya tulis ilmiah.',
                'file_path' => $file,
            ]);

        $response->assertRedirect(route('admin.final-projects.formats.index'));
        $this->assertDatabaseHas('final_project_formats', [
            'school_id' => $this->sma->id,
            'title' => 'Panduan Penelitian SMA',
        ]);

        $format = FinalProjectFormat::first();
        Storage::disk('public')->assertExists($format->file_path);

        // Delete format
        $deleteResponse = $this->actingAs($this->smaAdminUser)
            ->delete(route('admin.final-projects.formats.destroy', $format->id));

        $deleteResponse->assertRedirect(route('admin.final-projects.formats.index'));
        $this->assertDatabaseMissing('final_project_formats', [
            'id' => $format->id,
        ]);
        Storage::disk('public')->assertMissing($format->file_path);
    }

    public function test_admin_can_approve_or_reject_proposal()
    {
        $project = FinalProject::create([
            'student_id' => $this->smaStudent->id,
            'academic_year_id' => $this->academicYear->id,
            'type' => 'penelitian_ilmiah',
            'title' => 'Analisis Polusi Sungai Gunungsitoli',
            'abstract' => 'Penelitian kualitas air sungai.',
            'status' => 'pending',
        ]);

        // 1. Test approve with advisor
        $approveResponse = $this->actingAs($this->smaAdminUser)
            ->post(route('admin.final-projects.proposals.assign', $project->id), [
                'action' => 'approve',
                'advisor_id' => $this->smaTeacher->id,
            ]);

        $approveResponse->assertRedirect(route('admin.final-projects.proposals.index'));
        $this->assertDatabaseHas('final_projects', [
            'id' => $project->id,
            'advisor_id' => $this->smaTeacher->id,
            'status' => 'approved',
        ]);

        // Reset status for reject test
        $project->update(['status' => 'pending', 'advisor_id' => null]);

        // 2. Test reject with reason
        $rejectResponse = $this->actingAs($this->smaAdminUser)
            ->post(route('admin.final-projects.proposals.assign', $project->id), [
                'action' => 'reject',
                'rejection_reason' => 'Judul kurang spesifik.',
            ]);

        $rejectResponse->assertRedirect(route('admin.final-projects.proposals.index'));
        $this->assertDatabaseHas('final_projects', [
            'id' => $project->id,
            'status' => 'rejected',
            'rejection_reason' => 'Judul kurang spesifik.',
        ]);
    }

    public function test_multi_school_isolation_for_admin()
    {
        $projectSMK = FinalProject::create([
            'student_id' => $this->smkStudent->id,
            'academic_year_id' => $this->academicYear->id,
            'type' => 'project_akhir',
            'title' => 'Aplikasi Kasir SMK',
            'abstract' => 'Deskripsi aplikasi kasir.',
            'status' => 'pending',
        ]);

        // SMA Admin tries to approve SMK student proposal -> Should get 403 Forbidden
        $response = $this->actingAs($this->smaAdminUser)
            ->post(route('admin.final-projects.proposals.assign', $projectSMK->id), [
                'action' => 'approve',
                'advisor_id' => $this->smaTeacher->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_student_can_input_logbook_and_advisor_can_review()
    {
        $project = FinalProject::create([
            'student_id' => $this->smaStudent->id,
            'academic_year_id' => $this->academicYear->id,
            'type' => 'penelitian_ilmiah',
            'title' => 'Analisis Sungai',
            'abstract' => 'Deskripsi.',
            'advisor_id' => $this->smaTeacher->id,
            'status' => 'approved',
            'current_stage' => 'bab1',
        ]);

        // Student submits log
        $logResponse = $this->actingAs($this->smaStudentUser)
            ->post(route('siswa.final-project.log.store'), [
                'log_date' => date('Y-m-d'),
                'stage' => 'bab1',
                'activity' => 'Melakukan wawancara dengan warga sekitar sungai.',
            ]);

        $logResponse->assertRedirect(route('siswa.final-project.index'));
        $this->assertDatabaseHas('final_project_logs', [
            'final_project_id' => $project->id,
            'activity' => 'Melakukan wawancara dengan warga sekitar sungai.',
            'status' => 'submitted',
        ]);

        // Assert reputation points for student (+10 points)
        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $this->smaStudentUser->id,
            'points' => 10,
            'category' => 'final_project',
        ]);

        // Check project status auto transitions to 'in_progress'
        $this->assertEquals('in_progress', $project->fresh()->status);

        $log = FinalProjectLog::first();

        // Advisor reviews log
        $reviewResponse = $this->actingAs($this->smaTeacherUser)
            ->post(route('guru.final-projects.bimbingan.review-log', [$project->id, $log->id]), [
                'advisor_feedback' => 'Pertanyaan wawancara sudah baik, lanjutkan tabulasi data.',
            ]);

        $reviewResponse->assertRedirect(route('guru.final-projects.bimbingan.show', $project->id));
        $this->assertDatabaseHas('final_project_logs', [
            'id' => $log->id,
            'advisor_feedback' => 'Pertanyaan wawancara sudah baik, lanjutkan tabulasi data.',
            'status' => 'reviewed',
        ]);

        // Assert reputation points for advisor (+15 points)
        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $this->smaTeacherUser->id,
            'points' => 15,
            'category' => 'mentoring',
        ]);
    }

    public function test_advisor_can_approve_readiness_and_admin_schedules_exam()
    {
        $project = FinalProject::create([
            'student_id' => $this->smaStudent->id,
            'academic_year_id' => $this->academicYear->id,
            'type' => 'penelitian_ilmiah',
            'title' => 'Analisis Sungai',
            'abstract' => 'Deskripsi.',
            'advisor_id' => $this->smaTeacher->id,
            'status' => 'in_progress',
            'current_stage' => 'bab5',
        ]);

        // Advisor marks ready
        $readyResponse = $this->actingAs($this->smaTeacherUser)
            ->post(route('guru.final-projects.bimbingan.ready', $project->id));

        $readyResponse->assertRedirect(route('guru.final-projects.bimbingan.show', $project->id));
        $this->assertEquals('ready_for_exam', $project->fresh()->status);

        // Assert reputation points for student readiness (+50 points)
        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $this->smaStudentUser->id,
            'points' => 50,
            'category' => 'final_project',
        ]);

        // Admin schedules exam
        // We need an examiner (different teacher from same school)
        $examiner = Teacher::create([
            'school_id' => $this->sma->id,
            'user_id' => User::create([
                'name' => 'Guru Penguji',
                'email' => 'penguji@sma1pembda.sch.id',
                'password' => bcrypt('password'),
                'role' => 'guru',
                'school_id' => $this->sma->id,
            ])->id,
            'teacher_code' => 'T-PENGUJI',
            'full_name' => 'Guru Penguji',
            'gender' => 'L',
        ]);

        $scheduleResponse = $this->actingAs($this->smaAdminUser)
            ->post(route('admin.final-projects.exams.schedule', $project->id), [
                'exam_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'exam_location' => 'Laboratorium Biologi',
                'examiner_id' => $examiner->id,
            ]);

        $scheduleResponse->assertRedirect(route('admin.final-projects.exams.index'));
        $this->assertDatabaseHas('final_projects', [
            'id' => $project->id,
            'exam_location' => 'Laboratorium Biologi',
            'examiner_id' => $examiner->id,
        ]);
    }

    public function test_examiner_can_grade_final_project()
    {
        // Create another teacher as examiner
        $examiner = Teacher::create([
            'school_id' => $this->sma->id,
            'user_id' => User::create([
                'name' => 'Guru Penguji Ujian',
                'email' => 'penguji_ujian@sma1pembda.sch.id',
                'password' => bcrypt('password'),
                'role' => 'guru',
                'school_id' => $this->sma->id,
            ])->id,
            'teacher_code' => 'T-PENGUJI2',
            'full_name' => 'Guru Penguji Ujian',
            'gender' => 'L',
        ]);
        $examinerUser = $examiner->user;
        $examinerUser->update(['must_change_password' => false]);

        $project = FinalProject::create([
            'student_id' => $this->smaStudent->id,
            'academic_year_id' => $this->academicYear->id,
            'type' => 'penelitian_ilmiah',
            'title' => 'Analisis Sungai Ujian',
            'abstract' => 'Deskripsi.',
            'advisor_id' => $this->smaTeacher->id,
            'examiner_id' => $examiner->id,
            'exam_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'exam_location' => 'Lab',
            'status' => 'ready_for_exam',
        ]);

        $gradeResponse = $this->actingAs($examinerUser)
            ->post(route('guru.final-projects.ujian.grade', $project->id), [
                'grade' => 88.5,
                'grade_notes' => 'Penyusunan laporan sangat rapi, pemaparan hasil jelas.',
            ]);

        $gradeResponse->assertRedirect(route('guru.final-projects.ujian.index'));
        $this->assertDatabaseHas('final_projects', [
            'id' => $project->id,
            'grade' => 88.50,
            'grade_notes' => 'Penyusunan laporan sangat rapi, pemaparan hasil jelas.',
            'status' => 'completed',
        ]);

        // Assert reputation points for student passing exam
        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $this->smaStudentUser->id,
            'category' => 'final_project',
        ]);

        // Assert reputation points for examiner (+30 points)
        $this->assertDatabaseHas('reputation_logs', [
            'user_id' => $examinerUser->id,
            'points' => 30,
            'category' => 'examination',
        ]);
    }

    public function test_admin_can_create_cross_class_final_project_group_for_smk()
    {
        // 1. Create a second SMK Grade 12 classroom (XII TKJ 2)
        $smkClass2 = Classroom::create([
            'school_id' => $this->smk->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'XII-TKJ-2',
            'class_name' => 'XII TKJ 2',
            'class_type' => 'reguler',
            'grade_level' => 12,
            'is_active' => true,
        ]);

        // 2. Create second SMK student in XII TKJ 2
        $smkUser2 = User::create([
            'name' => 'Siswa SMK Kelas Lain',
            'email' => 'siswasmk2@smkpembda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'school_id' => $this->smk->id,
            'must_change_password' => false,
        ]);

        $smkStudent2 = Student::create([
            'school_id' => $this->smk->id,
            'user_id' => $smkUser2->id,
            'student_code' => 'S-SMK-2',
            'nisn' => '9988776655',
            'nis' => '20887766',
            'full_name' => 'Siswa SMK Kelas Lain',
            'gender' => 'L',
        ]);

        StudentClass::create([
            'student_id' => $smkStudent2->id,
            'classroom_id' => $smkClass2->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        // 3. Admin submits project proposal with members from 2 different classes ($this->smkStudent & $smkStudent2)
        $response = $this->actingAs($this->smkAdminUser)
            ->post(route('admin.final-projects.proposals.store'), [
                'title' => 'Project Akhir Lintas Kelas IoT Smart Home',
                'abstract' => 'Project kolaborasi antara XII TKJ 1 dan XII TKJ 2',
                'advisor_id' => $this->smkTeacher->id,
                'member_ids' => [$this->smkStudent->id, $smkStudent2->id],
                'leader_id' => $this->smkStudent->id,
            ]);

        $response->assertRedirect(route('admin.final-projects.proposals.index'));

        // Assert FinalProject was created
        $this->assertDatabaseHas('final_projects', [
            'student_id' => $this->smkStudent->id,
            'title' => 'Project Akhir Lintas Kelas IoT Smart Home',
            'type' => 'project_akhir',
            'advisor_id' => $this->smkTeacher->id,
            'status' => 'approved',
        ]);

        $project = FinalProject::where('title', 'Project Akhir Lintas Kelas IoT Smart Home')->first();

        // Assert 2 members from different classes were created
        $this->assertDatabaseHas('final_project_members', [
            'final_project_id' => $project->id,
            'student_id' => $this->smkStudent->id,
            'role' => 'leader',
        ]);

        $this->assertDatabaseHas('final_project_members', [
            'final_project_id' => $project->id,
            'student_id' => $smkStudent2->id,
            'role' => 'member',
        ]);
    }
}
