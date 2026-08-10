<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    protected function prepareForValidation()
    {
        // Jika password kosong, hapus dari request agar tidak memicu validasi
        if ($this->password === null || $this->password === '') {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        $roles = 'superadmin,admin_sekolah,kepala_sekolah,guru,siswa,orang_tua,bendahara,ketua_yayasan,pegawai';
        if ($this->user()->isAdminSekolah() && !$this->user()->isSuperAdmin()) {
            $roles = 'siswa,guru,pegawai,kepala_sekolah';
        }

        $rules = [
            'name' => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users,username,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
            'role' => 'required|in:' . $roles,
            'school_id' => 'nullable|exists:schools,id',
            'is_active' => 'boolean',
        ];

        if ($this->filled('password')) {
            $rules['password'] = ['confirmed', Password::defaults()];
        } else {
            $rules['password'] = ['nullable'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'username.unique' => 'Username sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
        ];
    }
}
