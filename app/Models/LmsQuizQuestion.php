<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsQuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'lms_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question',
        'question_type',
        'options',
        'correct_answer',
        'order_number',
        'score',
        'image_path',
        'video_url',
    ];

    protected $casts = [
        'options' => 'array',
        'score' => 'float',
        'order_number' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($question) {
            if ($question->question_type === 'true_false') {
                $question->correct_answer = $question->isTrueFalseBenar() ? 'true' : 'false';
                $question->options = null;
            }
        });
    }

    public function quiz()
    {
        return $this->belongsTo(LmsQuiz::class, 'quiz_id');
    }

    public function answers()
    {
        return $this->hasMany(LmsQuizAnswer::class, 'question_id');
    }

    public function isAutoGradable()
    {
        return in_array($this->question_type, ['multiple_choice', 'true_false', 'short_answer']);
    }

    /**
     * Check if true_false question's correct answer is Benar (true)
     */
    public function isTrueFalseBenar(): bool
    {
        if ($this->question_type !== 'true_false') {
            return false;
        }

        $val = strtolower(trim((string)$this->correct_answer));

        // 1. If options array is present (e.g. synced from CBT), check matching option text
        if (!empty($this->options) && is_array($this->options)) {
            foreach ($this->options as $opt) {
                $optKey = strtolower(trim((string)($opt['key'] ?? '')));
                if ($optKey === $val) {
                    $optText = strtolower(trim((string)($opt['text'] ?? '')));
                    if (in_array($optText, ['benar', 'true', 'ya', 'yes', 't', '1'])) {
                        return true;
                    }
                    if (in_array($optText, ['salah', 'false', 'tidak', 'no', 's', 'f', '0'])) {
                        return false;
                    }
                }
            }
        }

        // 2. Direct string value matching
        if (in_array($val, ['true', '1', 't', 'benar', 'ya', 'yes'])) {
            return true;
        }
        if (in_array($val, ['false', '0', 'f', 'salah', 'tidak', 'no', 's'])) {
            return false;
        }
        if ($val === 'a') {
            return true; // In CBT True/False import, Option A is Benar
        }
        if ($val === 'b') {
            if ($this->quiz && $this->quiz->question_package_id) {
                return false; // In CBT True/False import, Option B is Salah
            }
            return true; // Fallback: in Indonesian, 'b' is commonly abbreviation for 'benar'
        }

        return false;
    }

    /**
     * Get normalized correct answer ('true' or 'false' for true_false)
     */
    public function getNormalizedCorrectAnswerAttribute(): string
    {
        if ($this->question_type === 'true_false') {
            return $this->isTrueFalseBenar() ? 'true' : 'false';
        }
        return (string)$this->correct_answer;
    }

    /**
     * Get multiple choice options shuffled deterministically using a seed (e.g. attempt ID)
     */
    public function getShuffledOptions(?int $seed = null): ?array
    {
        $options = $this->options;
        if (!$options || !is_array($options)) {
            return $options;
        }

        if ($seed === null) {
            return $options;
        }

        // Deterministic Fisher-Yates shuffle using seed
        $shuffled = $options;
        $n = count($shuffled);
        if ($n <= 1) {
            return $shuffled;
        }

        // Pure LCG PRNG helper to avoid global state manipulation
        $rng = function(&$s) {
            $s = ($s * 1103515245 + 12345) & 0x7fffffff;
            return $s;
        };

        // Combine attempt seed with question ID for a unique sequence per question
        $currentSeed = $seed + $this->id;

        for ($i = $n - 1; $i > 0; $i--) {
            $currentSeed = $rng($currentSeed);
            $j = $currentSeed % ($i + 1);

            $temp = $shuffled[$i];
            $shuffled[$i] = $shuffled[$j];
            $shuffled[$j] = $temp;
        }

        return $shuffled;
    }
}
