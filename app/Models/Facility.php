<?php

namespace App\Models;

use App\Support\Slot;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'location', 'capacity', 'description', 'status'])]
class Facility extends Model
{
    use HasFactory;
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
     * Jalur berkas gambar fasilitas kampus.
     */
    public function imagePath(): ?string
    {
        return match ($this->name) {
            'Lapangan Basket' => 'images/fasilitas/lapangan-basket.webp',
            'Lapangan Futsal' => 'images/fasilitas/lapangan-futsal.webp',
            'Proyektor Epson EB-X51' => 'images/fasilitas/proyektor-epson-eb-x51.webp',
            'Sound System Portabel', 'Sound System Portable' => 'images/fasilitas/sound-system-portable.webp',
            'Aula Utama' => 'images/landing/widya-puraya.webp',
            'Aula Serbaguna Lantai 2' => 'images/landing/dekanat-ft.webp',
            default => match ($this->type) {
                'Ruang Kelas' => 'images/landing/gedung-manajemen.webp',
                'Laboratorium' => 'images/landing/lab-diplomasi.webp',
                'Aula' => 'images/landing/dekanat-ft.webp',
                default => null,
            },
        };
    }

    /**
     * URL aset gambar fasilitas.
     */
    public function getImageUrlAttribute(): ?string
    {
        $path = $this->imagePath();

        return $path ? asset($path) : null;
    }

    /**
     * Teks alternatif deskriptif untuk gambar fasilitas.
     */
    public function imageAlt(): string
    {
        return match ($this->name) {
            'Lapangan Basket' => 'Lapangan basket kampus Universitas Diponegoro Tembalang',
            'Lapangan Futsal' => 'Lapangan futsal kampus Universitas Diponegoro Tembalang',
            'Proyektor Epson EB-X51' => 'Proyektor Epson EB-X51 inventaris kampus Universitas Diponegoro',
            'Sound System Portabel', 'Sound System Portable' => 'Sound system portabel inventaris kampus Universitas Diponegoro',
            default => match ($this->type) {
                'Ruang Kelas' => 'Gedung perkuliahan dan ruang kelas Universitas Diponegoro Tembalang',
                'Laboratorium' => 'Laboratorium riset dan komputer Universitas Diponegoro Tembalang',
                'Aula' => 'Gedung aula dan pertemuan kampus Universitas Diponegoro Tembalang',
                default => 'Foto fasilitas '.$this->name.' Universitas Diponegoro Tembalang',
            },
        };
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
