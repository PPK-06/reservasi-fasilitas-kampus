<?php

namespace App\Models;

use App\Support\Slot;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable(['facility_id', 'purpose'])]
class Reservation extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * @return array<string, array{start: string, end: string, status: string, is_available: bool, is_booked: bool, is_past_limit: bool}>
     */
    public static function availability(Facility|int $facility, string|DateTimeInterface $date): array
    {
        return Slot::availability($facility, $date);
    }

    /**
     * D4 — Reservasi approved mendatang, dikelompokkan per fasilitas.
     *
     * Digunakan di halaman O5 (Detail Laporan — Petugas), O6 (Ketersediaan Fasilitas — Petugas),
     * A1 (Master Fasilitas — Admin), dan A5 (Detail Akun — Admin) untuk menampilkan
     * peringatan sebelum menonaktifkan akun atau mengubah status fasilitas.
     *
     * Hasil query selalu dikelompokkan per fasilitas (grouped by facility_id),
     * termasuk saat disaring berdasarkan pengguna tertentu (filter user).
     *
     * @param  Facility|int|null  $facility  Saring hanya untuk satu fasilitas (opsional, model atau ID).
     * @param  User|int|null  $user  Saring hanya untuk satu pengguna (opsional, model atau ID).
     * @return Collection<int, Collection<int, Reservation>>
     *                                                       Koleksi dikelompokkan per fasilitas: kunci luar = facility_id, kunci dalam = indeks numerik.
     */
    public static function upcomingApproved(
        Facility|int|null $facility = null,
        User|int|null $user = null,
    ): Collection {
        $query = static::where('status', 'approved')
            ->where('start_time', '>', now())
            ->with('user:id,name', 'facility:id,name')
            ->select('id', 'facility_id', 'user_id', 'start_time', 'end_time')
            ->orderBy('start_time');

        if ($facility !== null) {
            $query->where('facility_id', $facility instanceof Facility ? $facility->id : $facility);
        }

        if ($user !== null) {
            $query->where('user_id', $user instanceof User ? $user->id : $user);
        }

        return $query->get()->groupBy('facility_id');
    }
}
