<?php

namespace App\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreLmsMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge([
                'title' => trim((string) $this->input('title')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'module_id' => 'nullable|exists:lms_modules,id',
            'title' => 'required|string|max:255',
            'material_type' => 'required|in:pdf,document,video,text,image,link,interactive,audio,embed,canva,googledocs',
            'content' => 'nullable|string',
            'file_url' => 'nullable|string',
            'file' => 'nullable|file|max:30720',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul materi wajib diisi.',
            'title.max' => 'Judul materi maksimal 255 karakter.',
            'material_type.required' => 'Jenis materi wajib dipilih.',
            'file.max' => 'Ukuran berkas maksimal 30 MB.',
        ];
    }
}
