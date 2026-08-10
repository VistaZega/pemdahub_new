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
