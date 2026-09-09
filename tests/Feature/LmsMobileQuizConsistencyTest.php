<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\LmsClass;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\LmsQuizQuestion;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsMobileQuizConsistencyTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'SMA Swasta Pembda 1 Gunungsitoli',
            'type' => 'SMA',
            'npsn' => '20220002',
            'address' => 'Jl. Pendidikan No.1',
            'city' => 'Gunungsitoli',
            'province' => 'Sumatera Utara',
            'postal_code' => '22812',
            'phone' => '082168532568',
            'email' => 'info@sma1pembda.sch.id',
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::create([
            'year' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'semester_start' => '2025-07-15',
            'semester_end' => '2026-06-15',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_number' => 2,
            'semester_name' => 'Genap 2025/2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-06-15',
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'subject_code' => 'BIO',
            'subject_name' => 'Biologi',
            'description' => 'Pelajaran Biologi',
            'kkm' => 75,
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'class_code' => 'XI-IPA',
            'class_name' => 'XI-IPA',
            'grade_level' => 11,
            'capacity' => 32,
            'is_active' => true,
        ]);

        $this->guruUser = User::create([
            'name' => 'Guru Biologi',
            'username' => 'guru_bio',
            'email' => 'guru_bio@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'school_id' => $this->school->id,
            'employee_code' => 'EMP-BIO-001',
            'full_name' => 'Guru Biologi',
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
            'teacher_code' => 'GR-BIO-001',
            'full_name' => 'Guru Biologi',
            'gender' => 'L',
            'education_level' => 'S1',
            'major' => 'Biologi',
            'religion' => 'Kristen Protestan',
            'is_active' => true,
        ]);

        $this->siswaUser = User::create([
            'name' => 'Siswa Mobile Test',
            'username' => 'siswa_mob',
            'email' => 'siswa_mob@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        $this->student = Student::create([
            'user_id' => $this->siswaUser->id,
            'school_id' => $this->school->id,
            'classroom_id' => $this->classroom->id,
            'nisn' => '0098765432',
            'nis' => '20240099',
            'full_name' => 'Siswa Mobile Test',
            'gender' => 'L',
            'entry_year' => 2024,
            'enrollment_status' => 'active',
            'admission_date' => '2024-07-15',
            'is_active' => true,
        ]);

        $this->course = LmsCourse::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'course_name' => 'Biologi Sel XI',
            'is_active' => true,
        ]);

        $lmsClass = LmsClass::create([
            'school_id' => $this->school->id,
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
        ]);

        LmsEnrollment::create([
            'lms_class_id' => $lmsClass->id,
            'student_id' => $this->student->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);
    }

    /**
     * Test that bank of 62 questions is sampled down to 20 questions on both Mobile and Desktop
     */
    public function test_quiz_sampling_limits_questions_to_sample_count_on_mobile_and_desktop(): void
    {
        // 1. Create Quiz with 62 questions in bank, sample count = 20
        $quiz = LmsQuiz::create([
            'course_id' => $this->course->id,
            'title' => 'Kuis Uji Bank Soal 62 Butir',
            'time_limit' => 2,
            'passing_score' => 70,
            'max_attempts' => 3,
            'shuffle_questions' => true,
            'question_sample_count' => 20,
            'points_per_question' => 5,
            'total_score' => 100,
            'is_published' => true,
        ]);

        // Create 62 questions in the quiz
        for ($i = 1; $i <= 62; $i++) {
            LmsQuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => "Pertanyaan butir nomor {$i} dari bank soal",
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => "Pilihan A soal {$i}"],
                    ['key' => 'B', 'text' => "Pilihan B soal {$i}"],
                    ['key' => 'C', 'text' => "Pilihan C soal {$i}"],
                    ['key' => 'D', 'text' => "Pilihan D soal {$i}"],
                ],
                'correct_answer' => 'A',
                'order_number' => $i,
                'score' => 5,
            ]);
        }

        $this->assertEquals(62, $quiz->questions()->count());

        // 2. Test Mobile startQuiz loads exactly 20 questions
        $this->actingAs($this->siswaUser);
        session(['active_role' => 'siswa']);

        $mobileResponse = $this->get(route('mobile.lms.quiz.start', $quiz->id));
        $mobileResponse->assertStatus(200);

        $mobileQuestions = $mobileResponse->viewData('questions');
        $this->assertCount(20, $mobileQuestions, 'Mobile quiz must only load 20 sampled questions instead of 62');

        // 3. Test Desktop startQuiz loads exactly 20 questions
        $desktopResponse = $this->get(route('siswa.lms.quizzes.start', $quiz->id));
        $desktopResponse->assertStatus(200);

        $desktopQuestions = $desktopResponse->viewData('questions');
        $this->assertCount(20, $desktopQuestions, 'Desktop quiz must only load 20 sampled questions instead of 62');

        // Verify the attempt ID is the same and deterministic questions match
        $attempt = LmsQuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $this->student->id)->first();
        $this->assertNotNull($attempt);

        $mobileIds = collect($mobileQuestions)->pluck('id')->toArray();
        $desktopIds = collect($desktopQuestions)->pluck('id')->toArray();

        $this->assertEquals($desktopIds, $mobileIds, 'Question order and items must be identical between Desktop and Mobile for the same attempt');
    }

    /**
     * Test that countdown timer calculation and remainingSeconds are accurate
     */
    public function test_quiz_timer_remaining_seconds_is_passed_correctly_on_mobile(): void
    {
        $quiz = LmsQuiz::create([
            'course_id' => $this->course->id,
            'title' => 'Kuis Durasi 2 Menit',
            'time_limit' => 2, // 2 minutes = 120 seconds
            'passing_score' => 70,
            'is_published' => true,
        ]);

        LmsQuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Soal durasi',
            'question_type' => 'multiple_choice',
            'options' => ['A' => '1', 'B' => '2'],
            'correct_answer' => 'A',
            'score' => 10,
        ]);

        // Create attempt started 30 seconds ago
        $attempt = LmsQuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $this->student->id,
            'started_at' => now()->subSeconds(30),
        ]);

        $this->actingAs($this->siswaUser);
        session(['active_role' => 'siswa']);

        $response = $this->get(route('mobile.lms.quiz.start', $quiz->id));
        $response->assertStatus(200);

        $remainingSeconds = $response->viewData('remainingSeconds');
        // Remaining should be approximately 90 seconds (120 - 30)
        $this->assertNotNull($remainingSeconds);
        $this->assertGreaterThanOrEqual(88, $remainingSeconds);
        $this->assertLessThanOrEqual(91, $remainingSeconds);

        // Verify that the view renders the floating timer and javascript
        $response->assertSee('floatingTimerDisplay');
        $response->assertSee('01:3');
    }

    /**
     * Test mobile submitQuiz accurately grades sampled questions and updates attempt
     */
    public function test_mobile_quiz_submit_grades_accurately_with_sampling(): void
    {
        $quiz = LmsQuiz::create([
            'course_id' => $this->course->id,
            'title' => 'Kuis Skor Mobile Sampling',
            'time_limit' => 10,
            'passing_score' => 70,
            'question_sample_count' => 5,
            'points_per_question' => 20,
            'total_score' => 100,
            'is_published' => true,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            LmsQuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => "Soal nomor {$i}",
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Benar'],
                    ['key' => 'B', 'text' => 'Salah'],
                ],
                'correct_answer' => 'A',
                'order_number' => $i,
                'score' => 10,
            ]);
        }

        $attempt = LmsQuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
        ]);

        $this->actingAs($this->siswaUser);
        session(['active_role' => 'siswa']);

        // Fetch the sampled 5 questions for this attempt
        $questionsQuery = $quiz->questions()->inRandomOrder($attempt->id)->take(5);
        $sampledQuestions = $questionsQuery->get();
        $this->assertCount(5, $sampledQuestions);

        // Answer 4 out of 5 correctly (80%)
        $answers = [];
        foreach ($sampledQuestions as $idx => $q) {
            $answers[$q->id] = ($idx < 4) ? 'A' : 'B';
        }

        $response = $this->post(route('mobile.lms.quiz.submit', $attempt->id), [
            'answers' => $answers,
        ]);

        $response->assertRedirect(route('mobile.lms.quiz.result', $attempt->id));

        $attempt->refresh();
        $this->assertNotNull($attempt->finished_at);
        $this->assertEquals(80.0, (float)$attempt->score);
        $this->assertTrue($attempt->is_passed);
    }

    /**
     * Test that mobile quiz page hides global bottom navigation and renders both inline & sticky submit buttons
     */
    public function test_mobile_quiz_hides_bottom_navigation_and_shows_submit_buttons(): void
    {
        $quiz = LmsQuiz::create([
            'course_id' => $this->course->id,
            'title' => 'Kuis UI Mobile Test',
            'time_limit' => 15,
            'passing_score' => 75,
            'max_attempts' => 2,
            'is_published' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            LmsQuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => "Pertanyaan ke-{$i}",
                'question_type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Pilihan A'],
                    ['key' => 'B', 'text' => 'Pilihan B'],
                ],
                'correct_answer' => 'A',
                'order_number' => $i,
                'score' => 20,
            ]);
        }

        $this->actingAs($this->siswaUser);
        session(['active_role' => 'siswa']);

        $response = $this->get(route('mobile.lms.quiz.start', $quiz->id));
        $response->assertStatus(200);

        // 1. Bottom navigation bar must NOT be present on the quiz page
        $response->assertDontSee('<nav class="clay-nav', false);

        // 2. Both inline submit card & sticky submit bar must be present
        $response->assertSee('Akhir Lembar Soal Kuis');
        $response->assertSee('Kirim & Selesaikan Kuis', false);
        $response->assertSee('Kirim Jawaban');
        $response->assertSee('z-50');

        // 3. Meta notranslate must be present to prevent Google Translate overlay
        $response->assertSee('name="google" content="notranslate"', false);
    }
}
