<?php

namespace App\Models;

use App\Support\Slot;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'location', 'capacity', 'description', 'status'])]
class Facility extends Model
{
    /*
    |----------------------------------------------------------------------
    | Konstanta enum tingkat aplikasi (D1, D2, D3 + F6)
    |----------------------------------------------------------------------
    | Kolom type dan location di database adalah VARCHAR; nilai sahnya
    | dijaga konstanta ini + Rule::in() di Form Request.
    | Kolom status adalah ENUM MySQL, tapi konstantanya tetap ada di sini
    | supaya Blade tidak perlu hardcode daftar status.
    */

    /** @var array<string, string>  slug => label tampilan */
    public const TYPES = [
        'Ruang Kelas' => 'Ruang Kelas',
        'Aula' => 'Aula',
        'Laboratorium' => 'Laboratorium',
        'Lapangan' => 'Lapangan',
        'Alat' => 'Alat',
    ];

    /** @var array<string, string>  slug => label tampilan */
    public const LOCATIONS = [
        'Gedung A' => 'Gedung A',
        'Gedung B' => 'Gedung B',
        'Gedung C' => 'Gedung C',
        'Gedung Serba Guna' => 'Gedung Serba Guna',
        'Area Olahraga' => 'Area Olahraga',
        'Gudang Inventaris' => 'Gudang Inventaris',
    ];

    /** @var array<string, string>  slug => label tampilan */
    public const STATUSES = [
        'active' => 'Aktif',
        'under_maintenance' => 'Dalam Perbaikan',
        'inactive' => 'Nonaktif',
    ];

    /*
    |----------------------------------------------------------------------
    | Helper
    |----------------------------------------------------------------------
    */

    /**
     * Apakah fasilitas dapat dipesan saat ini? (D5)
     */
    public function isBookable(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Label status dalam bahasa Indonesia.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * @return array<string, array{start: string, end: string, status: string, is_available: bool, is_booked: bool, is_past_limit: bool}>
     */
    public function slotAvailability(string|DateTimeInterface $date): array
    {
        return Slot::availability($this, $date);
    }

    /*
    |----------------------------------------------------------------------
    | Relasi
    |----------------------------------------------------------------------
    */

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
