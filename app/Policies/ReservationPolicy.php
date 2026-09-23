<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

/**
 * Policy untuk Reservation — F4.
 * Laravel menemukan policy ini otomatis lewat konvensi nama.
 */
class ReservationPolicy
{
    /**
     * U3 — pengguna hanya boleh melihat reservasi miliknya sendiri.
     * Petugas dan admin boleh melihat semua (untuk O3).
     */
    public function view(User $user, Reservation $reservation): bool
    {
        if (in_array($user->role, ['petugas', 'admin'])) {
            return true;
        }

        return $user->id === $reservation->user_id;
    }

    /**
     * U3 — pengguna hanya boleh membatalkan reservasi miliknya sendiri.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id;
    }
}
