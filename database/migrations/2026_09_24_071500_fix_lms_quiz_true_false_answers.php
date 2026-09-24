<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use App\Models\LmsQuizAnswer;
use App\Models\LmsQuizAttempt;
use App\Models\CbtQuestion;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixes True/False quiz question keys imported from CBT bank and recalculates affected student attempts.
     */
    public function up(): void
    {
        // 1. Ambil semua soal LMS bertipe true_false
        $questions = LmsQuizQuestion::with('quiz')->where('question_type', 'true_false')->get();
        $affectedQuizIds = [];

        foreach ($questions as $question) {
            $quiz = $question->quiz;
            $isBenar = null;

            // Prioritas 1: Cek dari Bank Soal CBT asal jika kuis terhubung ke paket soal CBT
            if ($quiz && $quiz->question_package_id) {
                $cbtQuestion = CbtQuestion::with('options')
                    ->where('question_bank_id', $quiz->question_package_id)
                    ->where(function ($q) use ($question) {
                        $q->where('question_text', $question->question)
                          ->orWhere('question_text', trim($question->question));
                    })->first();

                if ($cbtQuestion) {
                    $correctOpt = $cbtQuestion->options->firstWhere('is_correct', true);
                    if ($correctOpt) {
                        $text = strtolower(trim((string)$correctOpt->option_text));
                        $label = strtoupper(trim((string)$correctOpt->option_label));

                        if (in_array($text, ['benar', 'true', 'ya', 'yes', 't', '1'])) {
                            $isBenar = true;
                        } elseif (in_array($text, ['salah', 'false', 'tidak', 'no', 's', 'f', '0'])) {
                            $isBenar = false;
                        } elseif ($label === 'T' || $label === 'A') {
                            $isBenar = true;
                        } elseif ($label === 'F' || $label === 'B') {
                            $isBenar = false;
                        }
                    }

                    if ($isBenar === null && !empty($cbtQuestion->answer_key)) {
                        $ak = strtolower(trim((string)$cbtQuestion->answer_key));
                        if (in_array($ak, ['true', '1', 't', 'benar', 'ya', 'yes', 'a'])) {
                            $isBenar = true;
                        } elseif (in_array($ak, ['false', '0', 'f', 'salah', 'tidak', 'no', 'b'])) {
                            $isBenar = false;
                        }
                    }
                }
            }

            // Prioritas 2: Cek options JSON yang tersimpan pada soal (hasil sync sebelumnya)
            if ($isBenar === null && !empty($question->options) && is_array($question->options)) {
                $val = strtolower(trim((string)$question->correct_answer));
                foreach ($question->options as $opt) {
                    $optKey = strtolower(trim((string)($opt['key'] ?? '')));
                    if ($optKey === $val) {
                        $optText = strtolower(trim((string)($opt['text'] ?? '')));
                        if (in_array($optText, ['benar', 'true', 'ya', 'yes'])) {
                            $isBenar = true;
                            break;
                        }
                        if (in_array($optText, ['salah', 'false', 'tidak', 'no'])) {
                            $isBenar = false;
                            break;
                        }
                    }
                }
            }

            // Prioritas 3: Deteksi nilai langsung pada correct_answer
            if ($isBenar === null) {
                $val = strtolower(trim((string)$question->correct_answer));
                if (in_array($val, ['true', '1', 't', 'benar', 'ya', 'yes', 'a'])) {
                    $isBenar = true;
                } elseif (in_array($val, ['false', '0', 'f', 'salah', 'tidak', 'no'])) {
                    $isBenar = false;
                } elseif ($val === 'b') {
                    // Jika terhubung ke CBT, option B adalah Salah; jika tidak, B adalah Benar
                    $isBenar = ($quiz && $quiz->question_package_id) ? false : true;
                } else {
                    $isBenar = true; // Fallback aman
                }
            }

            $newCorrectAnswer = $isBenar ? 'true' : 'false';

            // Update record soal LMS
            DB::table('lms_quiz_questions')
                ->where('id', $question->id)
                ->update([
                    'correct_answer' => $newCorrectAnswer,
                    'options' => null,
                    'updated_at' => now(),
                ]);

            if ($question->quiz_id) {
                $affectedQuizIds[$question->quiz_id] = true;
            }

            // 2. Koreksi jawaban siswa yang sudah pernah dikirimkan untuk soal ini
            $answers = LmsQuizAnswer::where('question_id', $question->id)->get();
            foreach ($answers as $ans) {
                $studentVal = strtolower(trim((string)$ans->answer));
                $studentNormalized = in_array($studentVal, ['true', '1', 't', 'b', 'benar', 'yes', 'y']) ? 'true' : 'false';
                $isCorrect = ($studentNormalized === $newCorrectAnswer);

                $pointVal = ($quiz && $quiz->points_per_question) ? $quiz->points_per_question : ($question->score ?: 1);
                $score = $isCorrect ? $pointVal : 0;

                DB::table('lms_quiz_answers')
                    ->where('id', $ans->id)
                    ->update([
                        'is_correct' => $isCorrect,
                        'score' => $score,
                        'updated_at' => now(),
                    ]);
            }
        }

        // 3. Hitung ulang skor dan kelulusan seluruh pengerjaan (attempt) kuis yang terdampak
        foreach (array_keys($affectedQuizIds) as $quizId) {
            $quiz = LmsQuiz::with('questions')->find($quizId);
            if (!$quiz) continue;

            $maxScore = $quiz->questions->sum('score') ?: 100;
            $attempts = LmsQuizAttempt::where('quiz_id', $quizId)->get();

            foreach ($attempts as $attempt) {
                $totalScore = LmsQuizAnswer::where('attempt_id', $attempt->id)->sum('score');
                $scorePercentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

                $attempt->update([
                    'score' => round($scorePercentage, 2),
                    'is_passed' => $scorePercentage >= ($quiz->passing_score ?? 75),
                ]);

                // Sinkronkan ke buku nilai jika ada GradeService
                try {
                    $gradeService = app(\App\Services\GradeService::class);
                    $gradeService->syncQuizAttemptToGrade($attempt);
                } catch (\Throwable $e) {
                    // Abaikan jika service tidak tersedia saat migrasi
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op on rollback
    }
};
