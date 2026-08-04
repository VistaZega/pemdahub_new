<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use Carbon\Carbon;

class MikrokontrolerLmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courseId = 101; // As per the requirements
        $course = LmsCourse::find($courseId);
        
        if (!$course) {
            $this->command->error("Course with ID 101 not found!");
            return;
        }

        $jsonPath = database_path('seeders/lms_export.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("File lms_export.json not found!");
            return;
        }

        $modulesData = json_decode(File::get($jsonPath), true);
        
        // Clear previous data for this course to avoid duplicates if re-run
        LmsModule::where('course_id', $courseId)->forceDelete();
        LmsMaterial::where('course_id', $courseId)->forceDelete();
        LmsAssignment::where('course_id', $courseId)->forceDelete();
        LmsQuiz::where('course_id', $courseId)->forceDelete();

        foreach ($modulesData as $index => $modData) {
            $sequence = $index + 1;
            
            // 1. Create Module
            $module = LmsModule::create([
                'course_id' => $courseId,
                'title' => $modData['title'],
                'description' => $modData['description'],
                'sequence' => $sequence,
                'is_active' => true,
                'is_sequential' => true,
            ]);

            // Combine content with Markdown to HTML styling (Tailwind Prose ready)
            $content = $this->formatContent($modData);

            // 2. Create Material (Text type with everything inside)
            LmsMaterial::create([
                'course_id' => $courseId,
                'module_id' => $module->id,
                'title' => 'Materi Lengkap: ' . $modData['title'],
                'content' => $content,
                'material_type' => 'text',
                'order_number' => 1,
                'is_published' => true,
            ]);

            // 3. Create Assignment
            LmsAssignment::create([
                'course_id' => $courseId,
                'module_id' => $module->id,
                'title' => 'Tugas Praktik: ' . $modData['title'],
                'description' => '<p>Silakan praktikkan materi yang telah dipelajari pada modul ini. Modifikasi kodenya agar lebih menarik, lalu unggah file <strong>.ino</strong> atau foto/video bukti rangkaian Anda telah berhasil menyala.</p>',
                'assignment_type' => 'file',
                'deadline' => Carbon::now()->addDays(7),
                'max_score' => 100,
                'is_published' => true,
                'allow_resubmit' => true,
                'max_resubmissions' => 3,
            ]);

            // 4. Create Quiz
            if (isset($modData['quiz'])) {
                $quiz = LmsQuiz::create([
                    'course_id' => $courseId,
                    'module_id' => $module->id,
                    'title' => 'Kuis Evaluasi: ' . $modData['title'],
                    'description' => 'Kerjakan kuis ini untuk menguji pemahaman Anda terhadap teori modul.',
                    'time_limit' => 15, // 15 minutes
                    'total_score' => 100,
                    'passing_score' => 70,
                    'max_attempts' => 3,
                    'shuffle_questions' => true,
                    'show_result' => true,
                    'is_published' => true,
                ]);

                $quizData = $modData['quiz'];
                
                $correctOptionString = $quizData['options'][$quizData['correctAnswer']];

                LmsQuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $quizData['question'],
                    'question_type' => 'multiple_choice',
                    'options' => $quizData['options'],
                    'correct_answer' => $correctOptionString,
                    'order_number' => 1,
                    'score' => 100, // 1 question, so 100 points
                ]);
            }

            $this->command->info("Seeded Module: " . $modData['title']);
        }
        
        $this->command->info("Successfully seeded 17 modules!");
    }

    private function formatContent($data)
    {
        // Fix image paths in visual section to point to LMS public directory
        $visual = $data['visual'] ?? '';
        $visual = preg_replace('/!\[(.*?)\]\(\/(.*?)\)/', '![$1](/lms/materials/$2)', $visual);
        
        $fullMarkdown = "";
        if (isset($data['theory'])) $fullMarkdown .= $data['theory'] . "\n\n";
        if (!empty($visual)) $fullMarkdown .= $visual . "\n\n";
        if (isset($data['practice'])) $fullMarkdown .= $data['practice'] . "\n\n";
        if (isset($data['conclusion'])) $fullMarkdown .= $data['conclusion'] . "\n\n";
        
        return Str::markdown($fullMarkdown);
    }
}
