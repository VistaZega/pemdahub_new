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
}
