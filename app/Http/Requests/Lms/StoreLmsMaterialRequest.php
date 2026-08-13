<?php

namespace App\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreLmsMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => 'nullable|exists:lms_modules,id',
            'title' => 'required|string|max:255',
            'material_type' => 'required|in:pdf,document,video,text,image,link,interactive,audio,embed,canva,googledocs',
            'content' => 'nullable|string',
            'file_url' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul materi wajib diisi.',
            'material_type.required' => 'Jenis materi wajib dipilih.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
