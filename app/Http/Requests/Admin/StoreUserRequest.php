<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $roles = 'superadmin,admin_sekolah,kepala_sekolah,guru,siswa,orang_tua,bendahara,ketua_yayasan,pegawai';
        if ($this->user()->isAdminSekolah() && !$this->user()->isSuperAdmin()) {
            $roles = 'siswa,guru,pegawai,kepala_sekolah';
        }

        return [
            'name' => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role' => 'required|in:' . $roles,
            'school_id' => 'nullable|exists:schools,id',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
        ];
    }
}
