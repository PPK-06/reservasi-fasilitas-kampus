<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Penjagaannya ada di middleware `guest` pada route `register.store`.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan P4, dokumen route bagian 16.
     *
     * `role` dan `status` sengaja tidak ada di sini maupun di form; keduanya
     * diisi controller. Kalau ikut form, orang bisa mendaftar sebagai admin
     * dengan mengubah HTML.
     *
     * `identity_number` required penuh di sini, berbeda dengan A4 yang memakai
     * required_if, karena form ini hanya dipakai role `pengguna` dan NIM/NIP
     * justru bahan utama yang diverifikasi admin (C4).
     *
     * Aturan `confirmed` mencari field bernama `password_confirmation`
     * berdasarkan nama, jadi nama itu harus persis demikian.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'identity_number' => ['required', 'string', 'max:30', 'unique:users,identity_number'],
            'user_type' => ['required', 'in:mahasiswa,dosen,staf'],
        ];
    }
}
