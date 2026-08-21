<?php

namespace App\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreLmsAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => 'required|exists:lms_modules,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'assignment_type' => 'nullable|in:file,text,file_text,link',
            'is_group_assignment' => 'nullable|boolean',
            'due_date' => 'nullable|date',
            'max_score' => 'required|numeric|min:1|max:100',
            'allow_resubmit' => 'sometimes|boolean',
            'max_resubmissions' => 'nullable|integer|min:1|max:5',
            'file' => 'nullable|file|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 200 karakter.',
            'max_score.required' => 'Skor maksimal wajib diisi.',
            'max_score.min' => 'Skor maksimal minimal 1.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
