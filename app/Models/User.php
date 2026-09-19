<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'identity_number', 'user_type'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * H1 — nama route landing setelah login, per role.
     * pengguna → P1, petugas → O1, admin → A3.
     *
     * @var array<string, string>
     */
    public const LANDING_ROUTES = [
        'pengguna' => 'facilities.index',
        'petugas' => 'officer.dashboard',
        'admin' => 'admin.users.index',
    ];

    /**
     * C2 — pesan penolakan per status akun yang tidak boleh masuk.
     * Teks mengikuti tabel dokumen route bagian 15.
     *
     * @var array<string, string>
     */
    public const STATUS_REJECTION_MESSAGES = [
        'pending' => 'Akun Anda masih menunggu verifikasi admin.',
        'rejected' => 'Registrasi Anda ditolak. Hubungi admin untuk klarifikasi.',
        'suspended' => 'Akun Anda dinonaktifkan. Hubungi admin.',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * R1 — satu pengguna boleh mengajukan nol sampai banyak reservasi.
     * FK: reservations.user_id
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * R2 — satu pengguna boleh membuat nol sampai banyak laporan.
     * FK: reports.user_id
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Nama route landing untuk role akun ini (H1).
     */
    public function landingRouteName(): string
    {
        return self::LANDING_ROUTES[$this->role];
    }

    /**
     * Pesan penolakan untuk status akun ini (C2), atau null kalau statusnya verified.
     */
    public function statusRejectionMessage(): ?string
    {
        return self::STATUS_REJECTION_MESSAGES[$this->status] ?? null;
    }
}
