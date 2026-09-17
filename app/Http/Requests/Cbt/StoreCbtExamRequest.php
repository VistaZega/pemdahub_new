<?php

namespace App\Http\Requests\Cbt;

use App\Models\CbtExam;
use Illuminate\Foundation\Http\FormRequest;

class StoreCbtExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => 'required|exists:subjects,id',
            'exam_title' => 'required|string|max:255',
            'exam_description' => 'nullable|string',
            'exam_type' => 'required|in:' . implode(',', CbtExam::CLASS_SCOPE_TYPES),
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5',
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_result' => 'boolean',
            'show_answer_key' => 'boolean',
            'allow_review' => 'boolean',
            'prevent_tab_switch' => 'boolean',
            'prevent_copy_paste' => 'boolean',
            'auto_sync_grade' => 'boolean',
            'requires_tuition_payment' => 'boolean',
            'tuition_month' => 'nullable|integer|min:1|max:12',
            'tuition_year' => 'nullable|integer|min:2020|max:2099',
            'access_code' => 'nullable|string|max:20',
            'question_banks' => 'required|array|min:1',
            'question_banks.*' => 'array',
            'question_banks.*.bank_id' => 'required|exists:cbt_question_banks,id',
            'question_banks.*.questions_to_pick' => 'required|integer|min:1',
            'classrooms' => 'required|array|min:1',
            'classrooms.*' => 'exists:classrooms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_title.required' => 'Judul ujian wajib diisi.',
            'exam_type.required' => 'Tipe ujian wajib dipilih.',
            'duration_minutes.required' => 'Durasi ujian wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal 5 menit.',
            'passing_score.required' => 'KKM wajib diisi.',
            'passing_score.max' => 'KKM maksimal 100.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'question_banks.required' => 'Pilih minimal 1 bank soal.',
            'classrooms.required' => 'Pilih minimal 1 kelas.',
        ];
    }
}
