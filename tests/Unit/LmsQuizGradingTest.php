<?php

namespace Tests\Unit;

use App\Models\LmsQuizQuestion;
use PHPUnit\Framework\TestCase;

class LmsQuizGradingTest extends TestCase
{
    /** @test */
    public function test_is_auto_gradable_includes_multiple_choice_true_false_and_short_answer()
    {
        $mc = new LmsQuizQuestion(['question_type' => 'multiple_choice']);
        $tf = new LmsQuizQuestion(['question_type' => 'true_false']);
        $sa = new LmsQuizQuestion(['question_type' => 'short_answer']);
        $essay = new LmsQuizQuestion(['question_type' => 'essay']);

        $this->assertTrue($mc->isAutoGradable());
        $this->assertTrue($tf->isAutoGradable());
        $this->assertTrue($sa->isAutoGradable());
        $this->assertFalse($essay->isAutoGradable());
    }

    /** @test */
    public function test_true_false_normalization_variants()
    {
        $normalizeTf = function($val) {
            $v = strtolower(trim($val));
            if (in_array($v, ['true', '1', 't', 'b', 'benar', 'yes', 'y'])) return 'true';
            if (in_array($v, ['false', '0', 'f', 's', 'salah', 'no', 'n'])) return 'false';
            return $v;
        };

        // Test Benar variants
        $this->assertEquals('true', $normalizeTf('true'));
        $this->assertEquals('true', $normalizeTf('1'));
        $this->assertEquals('true', $normalizeTf('Benar'));
        $this->assertEquals('true', $normalizeTf(' B '));

        // Test Salah variants
        $this->assertEquals('false', $normalizeTf('false'));
        $this->assertEquals('false', $normalizeTf('0'));
        $this->assertEquals('false', $normalizeTf('Salah'));
        $this->assertEquals('false', $normalizeTf(' s '));

        // Comparison tests
        $this->assertEquals($normalizeTf('Benar'), $normalizeTf('true'));
        $this->assertEquals($normalizeTf('1'), $normalizeTf('true'));
        $this->assertEquals($normalizeTf('Salah'), $normalizeTf('false'));
    }

    /** @test */
    public function test_short_answer_case_insensitive_trimming()
    {
        $studentAnswer = '  Pancasila  ';
        $correctAnswer = 'pancasila';

        $isCorrect = strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
        $this->assertTrue($isCorrect);
    }

    /** @test */
    public function test_effective_points_per_question_calculation()
    {
        $quizAuto = new \App\Models\LmsQuiz([
            'question_sample_count' => 25,
            'total_score' => 100,
        ]);
        $this->assertEquals(4.0, $quizAuto->getEffectivePointsPerQuestion());

        $quizManual = new \App\Models\LmsQuiz([
            'question_sample_count' => 20,
            'points_per_question' => 5.0,
            'total_score' => 100,
        ]);
        $this->assertEquals(5.0, $quizManual->getEffectivePointsPerQuestion());

        $quizFallback = new \App\Models\LmsQuiz([
            'total_score' => 100,
        ]);
        $this->assertEquals(5.0, $quizFallback->getEffectivePointsPerQuestion(20));
    }

    /** @test */
    public function test_quiz_level_score_accumulation_for_sampled_questions()
    {
        $quiz = new \App\Models\LmsQuiz([
            'question_sample_count' => 25,
            'total_score' => 100,
        ]);

        $effectivePoints = $quiz->getEffectivePointsPerQuestion();
        $this->assertEquals(4.0, $effectivePoints);

        $correctCount = 20; // 20 out of 25 answered correctly
        $totalRawScore = $correctCount * $effectivePoints; // 20 * 4 = 80
        $maxPossibleScore = 25 * $effectivePoints; // 25 * 4 = 100
        $scorePercentage = ($totalRawScore / $maxPossibleScore) * 100;

        $this->assertEquals(80.0, $totalRawScore);
        $this->assertEquals(100.0, $maxPossibleScore);
        $this->assertEquals(80.0, $scorePercentage);
    }

    /** @test */
    public function test_quiz_manual_points_per_question_scoring()
    {
        $quiz = new \App\Models\LmsQuiz([
            'question_sample_count' => 20,
            'points_per_question' => 5.0,
            'total_score' => 100,
        ]);

        $effectivePoints = $quiz->getEffectivePointsPerQuestion();
        $this->assertEquals(5.0, $effectivePoints);

        $correctCount = 18; // 18 out of 20 answered correctly
        $totalRawScore = $correctCount * $effectivePoints; // 18 * 5 = 90
        $maxPossibleScore = 20 * $effectivePoints; // 20 * 5 = 100
        $scorePercentage = ($totalRawScore / $maxPossibleScore) * 100;

        $this->assertEquals(90.0, $totalRawScore);
        $this->assertEquals(90.0, $scorePercentage);
    }
}
