<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:schools,id',
            'teacher_code' => 'required|string|max:50|unique:employees,employee_code',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'education_level' => 'nullable|in:SMA/SMK,D3,S1,S2,S3',
            'major' => 'nullable|string|max:100',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email_employee' => 'nullable|email',
            'is_active' => 'boolean',
            'employment_status' => 'required|in:yayasan,pns,pppk,honorer,percobaan,magang,kontrak',
            'tmt_date' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'marital_status' => 'required|in:menikah,belum_menikah',
            'children_count' => 'nullable|integer|min:0|max:10',
            'create_account' => 'boolean',
            'email' => 'required_if:create_account,1|nullable|email|unique:users,email',
            'password' => ['required_if:create_account,1', 'nullable', Password::defaults()],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'additional_school_ids' => 'nullable|array',
            'additional_school_ids.*' => 'exists:schools,id',
        ];
    }

    public function messages(): array
    {
        return [
            'school_id.required' => 'Sekolah wajib dipilih.',
            'teacher_code.required' => 'Kode guru wajib diisi.',
            'teacher_code.unique' => 'Kode guru sudah digunakan.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'employment_status.required' => 'Status kepegawaian wajib dipilih.',
            'tmt_date.required' => 'TMT wajib diisi.',
            'email.required_if' => 'Email wajib diisi jika membuat akun.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required_if' => 'Password wajib diisi jika membuat akun.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
