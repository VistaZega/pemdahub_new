<?php

namespace App\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreLmsQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => $this->isMethod('POST') ? 'required|exists:lms_modules,id' : 'nullable|exists:lms_modules,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1|max:10',
            'question_sample_count' => 'nullable|integer|min:1',
            'points_per_question' => 'nullable|numeric|min:0.01',
            'total_score' => 'nullable|numeric|min:1',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'shuffle_questions' => 'sometimes|boolean',
            'show_result' => 'sometimes|boolean',
            'is_published' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul quiz wajib diisi.',
            'passing_score.required' => 'Skor kelulusan wajib diisi.',
            'passing_score.max' => 'Skor kelulusan maksimal 100.',
            'end_time.after_or_equal' => 'Waktu selesai harus setelah atau sama dengan waktu mulai.',
        ];
    }
}
