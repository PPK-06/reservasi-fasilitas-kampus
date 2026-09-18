<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Password seragam untuk seluruh akun demo.
     */
    public const DEMO_PASSWORD = 'password';

    /**
     * Keadaan bawaan: pengguna berstatus verified, lengkap dengan
     * identity_number dan user_type sesuai C4.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => self::DEMO_PASSWORD,
            'role' => 'pengguna',
            'status' => 'verified',
            'identity_number' => fake()->unique()->numerify('23########'),
            'user_type' => fake()->randomElement(['mahasiswa', 'dosen', 'staf']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Akun petugas. C4: petugas tidak punya identity_number maupun user_type.
     */
    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'petugas',
            'identity_number' => null,
            'user_type' => null,
        ]);
    }

    /**
     * Akun admin. C4: admin tidak punya identity_number maupun user_type.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'identity_number' => null,
            'user_type' => null,
        ]);
    }

    /**
     * Akun hasil registrasi mandiri yang belum diverifikasi admin (C1).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Akun yang registrasinya ditolak admin (C2).
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Akun yang dinonaktifkan admin (C6).
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
