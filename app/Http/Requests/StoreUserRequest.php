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
     * Kosongkan NIM/NIP dan tipe pengguna saat role `petugas` (C4).
     *
     * Kontrak bagian 24 hanya mewajibkan keduanya untuk pengguna, tidak
     * melarangnya untuk petugas — dan field yang disembunyikan JavaScript tetap
     * bisa ikut terkirim lewat request yang dikirim langsung. Petugas tidak
     * punya NIM, jadi nilai apa pun di sana dibuang, bukan ditolak.
     *
     * Dibersihkan di sini, sebelum validasi, bukan di controller setelahnya.
     * Kalau dibersihkan di controller, `unique:users,identity_number` sempat
     * berjalan atas nilai yang toh akan dibuang, dan admin bisa mendapat error
     * "NIM/NIP sudah terpakai" saat membuat akun petugas.
     *
     * Kondisinya hanya `petugas`, sama dengan JavaScript di A4 yang
     * menyembunyikan dan men-disable kedua field hanya untuk role itu. Tidak
     * perlu melebarkannya ke role lain: nilai selain `pengguna` dan `petugas`,
     * termasuk role kosong, sudah ditolak `in:pengguna,petugas` dan akunnya
     * tidak pernah tersimpan.
     *
     * Kontraknya sendiri tidak diubah: `prohibited_unless` sengaja tidak
     * dipakai, karena ia menolak request alih-alih membersihkannya.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('role') === 'petugas') {
            $this->merge([
                'identity_number' => null,
                'user_type' => null,
            ]);
        }
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
            'identity_number' => ['required_if:role,pengguna', 'nullable', 'string', 'max:30', 'regex:/^[0-9]+$/', 'unique:users,identity_number'],
            'user_type' => ['required_if:role,pengguna', 'nullable', 'in:mahasiswa,dosen,staf'],
        ];
    }
}
