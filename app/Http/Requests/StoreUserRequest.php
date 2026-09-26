<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Penjagaannya ada di middleware `role:admin` pada grup route `/admin`.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan A4, dokumen route bagian 24.
     *
     * `admin` sengaja tidak ada di daftar `role`: US 13 dan 14 hanya menyebut
     * petugas dan pengguna, dan akun admin dibuat lewat seeder. `status` juga
     * tidak ada di sini maupun di form; controller mengisinya `verified` (C2).
     *
     * `identity_number` dan `user_type` nullable karena role, bukan karena
     * opsional (C4). `required_if` inilah satu-satunya penjaganya —
     * JavaScript di form hanya menyembunyikan field saat role = petugas.
     *
     * Aturan `confirmed` mencari field bernama `password_confirmation`
     * berdasarkan nama, jadi nama itu harus persis demikian.
     *
     * Pesan Bahasa Indonesia datang dari `lang/id/validation.php` (D17), bukan
     * dari `messages()` di sini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:pengguna,petugas'],
            'identity_number' => ['required_if:role,pengguna', 'nullable', 'string', 'max:30', 'unique:users,identity_number'],
            'user_type' => ['required_if:role,pengguna', 'nullable', 'in:mahasiswa,dosen,staf'],
        ];
    }
}
